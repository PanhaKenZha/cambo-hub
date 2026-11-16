<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public news listing, article pages and reader comments.
 */
class NewsController extends Controller
{
    /**
     * Newest live articles, optionally narrowed to one category, plus the most-read list.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate(['category' => ['nullable', 'string', 'max:100']]);

        $categories = NewsCategory::query()
            ->where('is_active', true)
            ->withCount(['articles' => fn (Builder $query) => $query->live()])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (NewsCategory $category): bool => $category->articles_count > 0)
            ->values();

        $selectedCategory = $categories->firstWhere('slug', $filters['category'] ?? null);

        return view('news.index', [
            'articles' => NewsArticle::live()
                ->with(['category', 'author'])
                ->when($selectedCategory, fn (Builder $query) => $query->where('news_category_id', $selectedCategory->id))
                ->orderByRaw('coalesce(published_at, created_at) desc')
                ->paginate(13)
                ->withQueryString(),
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'mostRead' => NewsArticle::live()->with('category')->where('views_count', '>', 0)->orderByDesc('views_count')->limit(5)->get(),
            'locale' => app()->getLocale(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $article = NewsArticle::live()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
        $article->increment('views_count');

        return view('news.show', [
            'article' => $article,
            'locale' => app()->getLocale(),
            'comments' => $article->comments()->with('user')->where('status', CommentStatus::Approved)->latest()->limit(100)->get(),
            'isPreview' => false,
            'relatedArticles' => NewsArticle::live()
                ->with('category')
                ->whereKeyNot($article->id)
                ->when($article->news_category_id, fn (Builder $query) => $query->where('news_category_id', $article->news_category_id))
                ->orderByRaw('coalesce(published_at, created_at) desc')
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Comments wait for a moderator before they appear.
     */
    public function comment(Request $request, string $slug): RedirectResponse
    {
        $article = NewsArticle::live()->where('slug', $slug)->firstOrFail();
        abort_unless($article->allow_comments, 403);

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:2000']]);

        $article->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'status' => CommentStatus::Pending,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', __('Thanks! Your comment will appear after a moderator reviews it.'));
    }
}
