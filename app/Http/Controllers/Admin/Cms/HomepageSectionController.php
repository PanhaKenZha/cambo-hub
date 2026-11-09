<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Enums\HomepageSectionType;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\Cms\Concerns\HandlesImageUploads;
use App\Http\Controllers\Admin\Cms\Concerns\ReordersItems;
use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    use HandlesImageUploads, ReordersItems;

    public function __construct(private AuditLogger $auditLogger) {}

    public function index(): View
    {
        return view('admin.cms.homepage.index', [
            'sections' => HomepageSection::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $type = HomepageSectionType::tryFrom((string) $request->query('type')) ?? HomepageSectionType::PromoBlock;

        return view('admin.cms.homepage.form', ['section' => new HomepageSection(['type' => $type, 'is_visible' => true, 'item_count' => 6])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'homepage');
        }

        $data['sort_order'] = (int) HomepageSection::max('sort_order') + 1;
        $section = HomepageSection::create($data);
        $this->auditLogger->log('cms.homepage_section.created', $section, newValues: $section->only(['type', 'title']));

        return redirect()->route('admin.cms.homepage.index')->with('status', 'Section added to the homepage.');
    }

    public function edit(HomepageSection $section): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        return view('admin.cms.homepage.form', ['section' => $section]);
    }

    public function update(Request $request, HomepageSection $section): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $data = $this->validatedData($request, $section);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'homepage', $section->image_path);
        }

        $oldValues = $section->only(['title', 'subtitle', 'is_visible', 'item_count', 'link_url']);
        $section->update($data);
        $this->auditLogger->log('cms.homepage_section.updated', $section, $oldValues, $section->only(array_keys($oldValues)));

        return redirect()->route('admin.cms.homepage.index')->with('status', 'Section saved.');
    }

    public function destroy(HomepageSection $section): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Delete);

        $this->auditLogger->log('cms.homepage_section.deleted', $section, $section->only(['type', 'title']));
        $this->deleteImage($section->image_path);
        $section->delete();

        return back()->with('status', 'Section removed from the homepage.');
    }

    public function move(Request $request, HomepageSection $section): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $this->moveItem(HomepageSection::orderBy('sort_order')->orderBy('id')->get(), $section, $direction);

        return back();
    }

    /**
     * Show or hide a section without opening it.
     */
    public function toggle(HomepageSection $section): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        $section->update(['is_visible' => ! $section->is_visible]);
        $this->auditLogger->log('cms.homepage_section.updated', $section, ['is_visible' => ! $section->is_visible], ['is_visible' => $section->is_visible]);

        return back()->with('status', $section->is_visible ? 'Section is now shown.' : 'Section is now hidden.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?HomepageSection $section = null): array
    {
        $data = $request->validate([
            'type' => [$section ? 'prohibited' : 'required', Rule::enum(HomepageSectionType::class)],
            'title.*' => ['nullable', 'string', 'max:150'],
            'subtitle.*' => ['nullable', 'string', 'max:300'],
            'button_label.*' => ['nullable', 'string', 'max:40'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/|\/)/'],
            'image' => $this->imageRules(required: false),
            'item_count' => ['nullable', 'integer', 'min:1', 'max:24'],
            'is_visible' => ['boolean'],
        ], ['link_url.regex' => 'Links must start with https://, http:// or /.']);

        $values = [
            'title' => HomepageSection::normalizeTranslations($data['title'] ?? []),
            'subtitle' => HomepageSection::normalizeTranslations($data['subtitle'] ?? []),
            'button_label' => HomepageSection::normalizeTranslations($data['button_label'] ?? []),
            'link_url' => $data['link_url'] ?? null,
            'item_count' => (int) ($data['item_count'] ?? 6),
            'is_visible' => $request->boolean('is_visible'),
        ];

        // The type is chosen when a section is added and cannot change afterwards.
        if ($section === null) {
            $values['type'] = $data['type'];
        }

        return $values;
    }
}
