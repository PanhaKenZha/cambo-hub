<?php

namespace App\Http\Controllers\Admin\News;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\Cms\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\NewsAuthor;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthorController extends Controller
{
    use HandlesImageUploads;

    public function __construct(private AuditLogger $auditLogger) {}

    public function index(): View
    {
        return view('admin.news.authors.index', [
            'authors' => NewsAuthor::with('user')->withCount('articles')->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);

        return view('admin.news.authors.form', ['author' => new NewsAuthor(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);
        $data = $this->validatedData($request);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeImage($request->file('avatar'), 'news/authors');
        }

        $author = NewsAuthor::create($data);
        $this->auditLogger->log('news.author.created', $author, newValues: $author->only(['name', 'user_id']));

        return redirect()->route('admin.news.authors.index')->with('status', 'Author added.');
    }

    public function edit(NewsAuthor $author): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);

        return view('admin.news.authors.form', ['author' => $author->load('user')]);
    }

    public function update(Request $request, NewsAuthor $author): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);
        $data = $this->validatedData($request, $author);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeImage($request->file('avatar'), 'news/authors', $author->avatar_path);
        }

        $oldValues = $author->only(['name', 'user_id', 'is_active']);
        $author->update($data);
        $this->auditLogger->log('news.author.updated', $author, $oldValues, $author->only(array_keys($oldValues)));

        return redirect()->route('admin.news.authors.index')->with('status', 'Author saved.');
    }

    public function destroy(NewsAuthor $author): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Delete);

        $this->auditLogger->log('news.author.deleted', $author, $author->only(['name', 'user_id']));
        $this->deleteImage($author->avatar_path);
        $author->delete();

        return back()->with('status', 'Author deleted. Their articles now have no byline.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?NewsAuthor $author = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'bio.*' => ['nullable', 'string', 'max:500'],
            'user_email' => ['nullable', 'email', Rule::exists('users', 'email')],
            'avatar' => $this->imageRules(required: false),
            'is_active' => ['boolean'],
        ], ['user_email.exists' => 'No account uses this email address.']);

        $userId = filled($data['user_email'] ?? null) ? User::where('email', $data['user_email'])->value('id') : null;

        if ($userId !== null && NewsAuthor::where('user_id', $userId)->whereKeyNot($author?->id)->exists()) {
            throw ValidationException::withMessages(['user_email' => 'This account is already linked to another author.']);
        }

        return [
            'name' => $data['name'],
            'slug' => $author?->slug ?? Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'bio' => NewsAuthor::normalizeTranslations($data['bio'] ?? []),
            'user_id' => $userId,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
