<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Enums\BannerPlacement;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\Cms\Concerns\HandlesImageUploads;
use App\Http\Controllers\Admin\Cms\Concerns\ReordersItems;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BannerController extends Controller
{
    use HandlesImageUploads, ReordersItems;

    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $placement = BannerPlacement::tryFrom((string) $request->query('placement'));
        $placementOrder = array_flip(array_column(BannerPlacement::cases(), 'value'));

        $banners = Banner::query()
            ->when($placement, fn ($query) => $query->where('placement', $placement))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->sortBy(fn (Banner $banner): int => $placementOrder[$banner->placement->value], SORT_NUMERIC)
            ->values();

        return view('admin.cms.banners.index', [
            'banners' => $banners,
            'activePlacement' => $placement,
            'placementCounts' => Banner::query()->toBase()->selectRaw('placement, count(*) as total')->groupBy('placement')->pluck('total', 'placement'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $placement = BannerPlacement::tryFrom((string) $request->query('placement')) ?? BannerPlacement::HomepageHero;

        return view('admin.cms.banners.form', ['banner' => new Banner(['placement' => $placement, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $data = $this->validatedData($request, isCreating: true);
        $data['image_path'] = $this->storeImage($request->file('image'), 'banners');
        $data['sort_order'] = (int) Banner::where('placement', $data['placement'])->max('sort_order') + 1;

        $banner = Banner::create($data);
        $this->auditLogger->log('cms.banner.created', $banner, newValues: $banner->only(['placement', 'title', 'link_url', 'starts_at', 'ends_at']));

        return redirect()->route('admin.cms.banners.index')->with('status', 'Banner added.');
    }

    public function edit(Banner $banner): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        return view('admin.cms.banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $data = $this->validatedData($request, isCreating: false);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'banners', $banner->image_path);
        }

        $oldValues = $banner->only(['placement', 'title', 'link_url', 'is_active', 'starts_at', 'ends_at']);
        $banner->update($data);
        $this->auditLogger->log('cms.banner.updated', $banner, $oldValues, $banner->only(array_keys($oldValues)));

        return redirect()->route('admin.cms.banners.index')->with('status', 'Banner saved.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Delete);

        $this->auditLogger->log('cms.banner.deleted', $banner, $banner->only(['placement', 'title']));
        $this->deleteImage($banner->image_path);
        $banner->delete();

        return back()->with('status', 'Banner deleted.');
    }

    public function move(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $this->moveItem(Banner::where('placement', $banner->placement)->orderBy('sort_order')->orderBy('id')->get(), $banner, $direction);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, bool $isCreating): array
    {
        $data = $request->validate([
            'placement' => ['required', Rule::enum(BannerPlacement::class)],
            'title.en' => ['required', 'string', 'max:150'],
            'title.km' => ['nullable', 'string', 'max:150'],
            'subtitle.*' => ['nullable', 'string', 'max:300'],
            'button_label.*' => ['nullable', 'string', 'max:40'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/|\/)/'],
            'image' => $this->imageRules(required: $isCreating),
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ], ['link_url.regex' => 'Links must start with https://, http:// or /.'], ['title.en' => 'English title', 'ends_at' => 'end date']);

        return [
            'placement' => $data['placement'],
            'title' => Banner::normalizeTranslations($data['title']),
            'subtitle' => Banner::normalizeTranslations($data['subtitle'] ?? []),
            'button_label' => Banner::normalizeTranslations($data['button_label'] ?? []),
            'link_url' => $data['link_url'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }
}
