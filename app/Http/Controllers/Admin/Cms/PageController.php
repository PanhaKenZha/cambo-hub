<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Enums\ContentStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
        ]);

        $pages = Page::query()
            ->with('editor')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(fn ($query) => $query->where('slug', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms.pages.index', ['pages' => $pages, 'filters' => $filters]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);

        return view('admin.cms.pages.form', ['page' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $data = $this->validatedData($request);
        $this->ensureCanPublish($request, null, $data);

        $page = Page::create([...$data, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('cms.page.created', $page, newValues: ['slug' => $page->slug, 'title' => $page->title, 'status' => $page->status->value]);

        return redirect()->route('admin.cms.pages.edit', $page)->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        return view('admin.cms.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $data = $this->validatedData($request, $page);
        $this->ensureCanPublish($request, $page, $data);

        $oldValues = $page->only(['slug', 'title', 'status', 'published_at', 'unpublish_at']);
        $page->update([...$data, 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('cms.page.updated', $page, $oldValues, $page->only(array_keys($oldValues)));

        return back()->with('status', 'Page saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Delete);

        $this->auditLogger->log('cms.page.deleted', $page, ['slug' => $page->slug, 'title' => $page->title]);
        $page->delete();

        return redirect()->route('admin.cms.pages.index')->with('status', 'Page deleted.');
    }

    /**
     * Show the page as visitors will see it, including drafts.
     */
    public function preview(Request $request, Page $page): View
    {
        $locale = in_array($request->query('lang'), array_keys(Page::translationLocales()), true) ? $request->query('lang') : 'en';

        return view('pages.show', [
            'title' => $page->translate('title', $locale),
            'html' => $page->bodyHtml($locale),
            'metaDescription' => $page->translate('meta_description', $locale),
            'isPreview' => true,
            'page' => $page,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Page $page = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title.en', ''))]);

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'title.en' => ['required', 'string', 'max:200'],
            'title.km' => ['nullable', 'string', 'max:200'],
            'body.en' => ['nullable', 'string', 'max:100000'],
            'body.km' => ['nullable', 'string', 'max:100000'],
            'meta_title.*' => ['nullable', 'string', 'max:70'],
            'meta_description.*' => ['nullable', 'string', 'max:160'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'unpublish_at' => ['nullable', 'date', 'after:published_at'],
        ], [], ['title.en' => 'English title', 'unpublish_at' => 'unpublish date']);

        return [
            'slug' => $data['slug'],
            'title' => Page::normalizeTranslations($data['title'] ?? []),
            'body' => Page::normalizeTranslations($data['body'] ?? []),
            'meta_title' => Page::normalizeTranslations($data['meta_title'] ?? []),
            'meta_description' => Page::normalizeTranslations($data['meta_description'] ?? []),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? ($data['status'] === ContentStatus::Published->value ? ($page?->published_at ?? now()) : null),
            'unpublish_at' => $data['unpublish_at'] ?? null,
        ];
    }

    /**
     * Publishing, unpublishing or rescheduling a live page needs the "cms.manage" permission.
     *
     * @param  array<string, mixed>  $data
     */
    private function ensureCanPublish(Request $request, ?Page $page, array $data): void
    {
        if ($request->user()->can(PermissionArea::WebsiteContent->permission(PermissionAction::Manage))) {
            return;
        }

        $normalize = fn (mixed $date): ?string => $date ? Carbon::parse($date)->toDateTimeString() : null;
        $wasPublished = $page?->status === ContentStatus::Published;
        $willBePublished = $data['status'] === ContentStatus::Published->value;
        $datesChanged = $page !== null && $willBePublished
            && ($normalize($page->published_at) !== $normalize($data['published_at'])
                || $normalize($page->unpublish_at) !== $normalize($data['unpublish_at']));

        if ($wasPublished !== $willBePublished || $datesChanged) {
            throw ValidationException::withMessages(['status' => 'You need the "publish" (Website content › Manage) permission to publish, unpublish or reschedule pages.']);
        }
    }
}
