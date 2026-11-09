<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Enums\MenuLocation;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\Cms\Concerns\ReordersItems;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    use ReordersItems;

    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $location = MenuLocation::tryFrom((string) $request->query('location')) ?? MenuLocation::Header;

        return view('admin.cms.menus.index', [
            'location' => $location,
            'items' => MenuItem::with(['page', 'children.page'])
                ->where('location', $location)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $location = MenuLocation::tryFrom((string) $request->query('location')) ?? MenuLocation::Header;

        return view('admin.cms.menus.form', $this->formData(new MenuItem(['location' => $location, 'is_visible' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);
        $data = $this->validatedData($request);
        $data['sort_order'] = (int) MenuItem::where('location', $data['location'])->where('parent_id', $data['parent_id'])->max('sort_order') + 1;

        $item = MenuItem::create($data);
        $this->auditLogger->log('cms.menu_item.created', $item, newValues: $item->only(['location', 'label', 'url', 'page_id']));

        return redirect()->route('admin.cms.menus.index', ['location' => $item->location->value])->with('status', 'Menu item added.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        return view('admin.cms.menus.form', $this->formData($menuItem));
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $data = $this->validatedData($request, $menuItem);

        $oldValues = $menuItem->only(['label', 'url', 'page_id', 'parent_id', 'is_visible']);
        $menuItem->update($data);
        $this->auditLogger->log('cms.menu_item.updated', $menuItem, $oldValues, $menuItem->only(array_keys($oldValues)));

        return redirect()->route('admin.cms.menus.index', ['location' => $menuItem->location->value])->with('status', 'Menu item saved.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Delete);

        $this->auditLogger->log('cms.menu_item.deleted', $menuItem, $menuItem->only(['location', 'label', 'url', 'page_id']));
        $menuItem->delete();

        return back()->with('status', 'Menu item removed.');
    }

    /**
     * Move an item one place up or down among its siblings.
     */
    public function move(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $this->moveItem(
            MenuItem::where('location', $menuItem->location)->where('parent_id', $menuItem->parent_id)->orderBy('sort_order')->orderBy('id')->get(),
            $menuItem,
            $direction,
        );

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?MenuItem $menuItem = null): array
    {
        $data = $request->validate([
            'location' => ['required', Rule::enum(MenuLocation::class)],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')->whereNull('parent_id')->where('location', $request->input('location')),
                Rule::notIn(array_filter([$menuItem?->id])),
            ],
            'label.en' => ['required', 'string', 'max:60'],
            'label.km' => ['nullable', 'string', 'max:60'],
            'link_type' => ['required', Rule::in(['page', 'url'])],
            'page_id' => ['nullable', 'required_if:link_type,page', 'integer', Rule::exists('pages', 'id')],
            'url' => ['nullable', 'required_if:link_type,url', 'string', 'max:500', 'regex:/^(https?:\/\/|\/|#)/'],
            'opens_in_new_tab' => ['boolean'],
            'is_visible' => ['boolean'],
        ], ['url.regex' => 'Links must start with https://, http:// or /.'], ['label.en' => 'English label']);

        if ($menuItem?->children()->exists() && filled($data['parent_id'] ?? null)) {
            throw ValidationException::withMessages(['parent_id' => 'An item with sub-items cannot itself become a sub-item.']);
        }

        return [
            'location' => $data['location'],
            'parent_id' => $data['parent_id'] ?? null,
            'label' => MenuItem::normalizeTranslations($data['label']),
            'page_id' => $data['link_type'] === 'page' ? $data['page_id'] : null,
            'url' => $data['link_type'] === 'url' ? $data['url'] : null,
            'opens_in_new_tab' => $request->boolean('opens_in_new_tab'),
            'is_visible' => $request->boolean('is_visible'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MenuItem $menuItem): array
    {
        return [
            'menuItem' => $menuItem,
            'pages' => Page::orderBy('slug')->get(),
            'parents' => MenuItem::where('location', $menuItem->location)->whereNull('parent_id')->whereKeyNot($menuItem->id)->orderBy('sort_order')->get(),
        ];
    }
}
