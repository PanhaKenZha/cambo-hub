<?php

namespace App\Http\Controllers\Admin\News;

use App\Enums\ArticleStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\Cms\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    use HandlesImageUploads;

    /**
     * Tabs on the article list: key => label.
     */
    private const array TABS = [
        'all' => 'All',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
        'draft' => 'Drafts',
        'in_review' => 'In review',
        'rejected' => 'Rejected',
        'featured' => 'Featured',
    ];

    public function __construct(private AuditLogger $auditLogger, private ApprovalService $approvals) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(array_keys(self::TABS))],
            'category' => ['nullable', 'integer'],
            'author' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $tab = $filters['tab'] ?? 'all';

        $articles = $this->tabQuery($tab)
            ->with(['category', 'author'])
            ->when($filters['category'] ?? null, fn ($query, int $category) => $query->where('news_category_id', $category))
            ->when($filters['author'] ?? null, fn ($query, int $author) => $query->where('news_author_id', $author))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->orderByRaw('coalesce(published_at, updated_at) desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.news.articles.index', [
            'articles' => $articles,
            'tabs' => self::TABS,
            'activeTab' => $tab,
            'tabCounts' => collect(array_keys(self::TABS))->mapWithKeys(fn (string $key): array => [$key => $this->tabQuery($key)->count()]),
            'categories' => NewsCategory::orderBy('sort_order')->get(),
            'authors' => NewsAuthor::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);

        return view('admin.news.articles.form', $this->formData(new NewsArticle(['allow_comments' => true, 'status' => ArticleStatus::Draft])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);
        $data = $this->validatedData($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $this->storeImage($request->file('cover_image'), 'news');
        }

        $article = NewsArticle::create([...$data, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('news.article.created', $article, newValues: ['title' => $article->title, 'status' => $article->status->value]);

        return $this->afterSave($request, $article, 'Article created.');
    }

    public function edit(NewsArticle $article): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);

        return view('admin.news.articles.form', $this->formData($article->load(['approvalRequests' => fn ($query) => $query->latest('id')])));
    }

    public function update(Request $request, NewsArticle $article): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);
        $data = $this->validatedData($request, $article);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $this->storeImage($request->file('cover_image'), 'news', $article->cover_image_path);
        }

        $oldValues = $article->only(['title', 'status', 'published_at', 'is_featured', 'news_category_id']);
        $article->update([...$data, 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('news.article.updated', $article, $oldValues, $article->only(array_keys($oldValues)));

        // An editor who can publish has decided the article; close any open review for it.
        $pendingReview = $article->approvalRequests()->pending()->first();
        if ($pendingReview && $article->status === ArticleStatus::Published && $request->user()->can('review', $pendingReview)) {
            $this->approvals->approve($pendingReview, $request->user(), 'Published from the article editor.');
        }

        return $this->afterSave($request, $article, 'Article saved.');
    }

    public function destroy(NewsArticle $article): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Delete);

        $this->auditLogger->log('news.article.deleted', $article, ['title' => $article->title, 'slug' => $article->slug]);
        $this->deleteImage($article->cover_image_path);
        $article->delete();

        return redirect()->route('admin.news.articles.index')->with('status', 'Article deleted.');
    }

    /**
     * Send a draft to the Approval center for an editor with publishing rights.
     */
    public function submit(Request $request, NewsArticle $article): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);
        $this->submitForReview($request->user(), $article);

        return back()->with('status', 'Article sent for review. An editor will publish or return it.');
    }

    public function toggleFeatured(NewsArticle $article): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Manage);

        $article->update(['is_featured' => ! $article->is_featured]);
        $this->auditLogger->log('news.article.updated', $article, ['is_featured' => ! $article->is_featured], ['is_featured' => $article->is_featured]);

        return back()->with('status', $article->is_featured ? 'Article featured on the homepage.' : 'Article no longer featured.');
    }

    /**
     * Show the article as readers will see it, including drafts.
     */
    public function preview(Request $request, NewsArticle $article): View
    {
        $locale = in_array($request->query('lang'), array_keys(NewsArticle::translationLocales()), true) ? $request->query('lang') : 'en';

        return view('news.show', [
            'article' => $article->load(['category', 'author']),
            'locale' => $locale,
            'comments' => collect(),
            'isPreview' => true,
        ]);
    }

    private function afterSave(Request $request, NewsArticle $article, string $message): RedirectResponse
    {
        if ($request->input('intent') === 'submit') {
            $this->submitForReview($request->user(), $article);
            $message = 'Article saved and sent for review.';
        }

        return redirect()->route('admin.news.articles.edit', $article)->with('status', $message);
    }

    private function submitForReview(User $user, NewsArticle $article): void
    {
        if (! in_array($article->status, [ArticleStatus::Draft, ArticleStatus::Rejected], true)) {
            throw ValidationException::withMessages(['status' => 'Only drafts and rejected articles can be sent for review.']);
        }

        $article->update(['status' => ArticleStatus::InReview, 'submitted_by' => $article->submitted_by ?? $user->id]);
        $this->approvals->submit($article, $user, $article->translate('excerpt', 'en') ?: null);
        $this->auditLogger->log('news.article.submitted', $article, ['status' => ArticleStatus::Draft->value], ['status' => ArticleStatus::InReview->value]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?NewsArticle $article = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title.en', ''))]);
        $canPublish = $request->user()->can(PermissionArea::News->permission(PermissionAction::Manage));

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('news_articles', 'slug')->ignore($article?->id)],
            'title.en' => ['required', 'string', 'max:200'],
            'title.km' => ['nullable', 'string', 'max:200'],
            'excerpt.*' => ['nullable', 'string', 'max:300'],
            'body.en' => ['required', 'string', 'max:100000'],
            'body.km' => ['nullable', 'string', 'max:100000'],
            'meta_title.*' => ['nullable', 'string', 'max:70'],
            'meta_description.*' => ['nullable', 'string', 'max:160'],
            'news_category_id' => ['nullable', 'integer', Rule::exists('news_categories', 'id')],
            'news_author_id' => ['nullable', 'integer', Rule::exists('news_authors', 'id')],
            'cover_image' => $this->imageRules(required: false),
            'allow_comments' => ['boolean'],
            'is_featured' => ['boolean'],
            'status' => ['nullable', Rule::in([ArticleStatus::Draft->value, ArticleStatus::Published->value])],
            'published_at' => ['nullable', 'date'],
        ], [], ['title.en' => 'English title', 'body.en' => 'English article text', 'news_category_id' => 'category', 'news_author_id' => 'author']);

        $values = [
            'slug' => $data['slug'],
            'title' => NewsArticle::normalizeTranslations($data['title']),
            'excerpt' => NewsArticle::normalizeTranslations($data['excerpt'] ?? []),
            'body' => NewsArticle::normalizeTranslations($data['body']),
            'meta_title' => NewsArticle::normalizeTranslations($data['meta_title'] ?? []),
            'meta_description' => NewsArticle::normalizeTranslations($data['meta_description'] ?? []),
            'news_category_id' => $data['news_category_id'] ?? null,
            'news_author_id' => $data['news_author_id'] ?? null,
            'allow_comments' => $request->boolean('allow_comments'),
        ];

        // Status, schedule and featuring are publishing decisions: only editors with news.manage make them.
        if ($canPublish) {
            $status = ArticleStatus::from($data['status'] ?? ($article?->status === ArticleStatus::Published ? 'published' : 'draft'));
            $values['status'] = $status;
            $values['published_at'] = $status === ArticleStatus::Published ? ($data['published_at'] ?? $article?->published_at ?? now()) : ($data['published_at'] ?? null);
            $values['is_featured'] = $request->boolean('is_featured');
        } elseif ($article === null) {
            $values['status'] = ArticleStatus::Draft;
        }

        return $values;
    }

    /**
     * @return Builder<NewsArticle>
     */
    private function tabQuery(string $tab): Builder
    {
        $query = NewsArticle::query();

        return match ($tab) {
            'published' => $query->live(),
            'scheduled' => $query->where('status', ArticleStatus::Published)->where('published_at', '>', now()),
            'draft' => $query->where('status', ArticleStatus::Draft),
            'in_review' => $query->where('status', ArticleStatus::InReview),
            'rejected' => $query->where('status', ArticleStatus::Rejected),
            'featured' => $query->where('is_featured', true),
            default => $query,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(NewsArticle $article): array
    {
        return [
            'article' => $article,
            'categories' => NewsCategory::orderBy('sort_order')->get(),
            'authors' => NewsAuthor::where('is_active', true)->orWhere('id', $article->news_author_id)->orderBy('name')->get(),
            'latestReview' => $article->exists ? $article->approvalRequests->first() : null,
        ];
    }
}
