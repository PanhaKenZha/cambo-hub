<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Enums\AnnouncementLevel;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(): View
    {
        return view('admin.cms.announcements.index', [
            'announcements' => Announcement::with('creator')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);

        return view('admin.cms.announcements.form', ['announcement' => new Announcement(['level' => AnnouncementLevel::Info, 'is_active' => true, 'is_dismissible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Create);

        $announcement = Announcement::create([...$this->validatedData($request), 'created_by' => $request->user()->id]);
        $this->auditLogger->log('cms.announcement.created', $announcement, newValues: $announcement->only(['message', 'level', 'starts_at', 'ends_at']));

        return redirect()->route('admin.cms.announcements.index')->with('status', 'Announcement created.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        return view('admin.cms.announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Edit);

        $oldValues = $announcement->only(['message', 'level', 'is_active', 'starts_at', 'ends_at']);
        $announcement->update($this->validatedData($request));
        $this->auditLogger->log('cms.announcement.updated', $announcement, $oldValues, $announcement->only(array_keys($oldValues)));

        return redirect()->route('admin.cms.announcements.index')->with('status', 'Announcement saved.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::WebsiteContent, PermissionAction::Delete);

        $this->auditLogger->log('cms.announcement.deleted', $announcement, $announcement->only(['message', 'level']));
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'message.en' => ['required', 'string', 'max:300'],
            'message.km' => ['nullable', 'string', 'max:300'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/|\/)/'],
            'link_label.*' => ['nullable', 'string', 'max:40'],
            'level' => ['required', Rule::enum(AnnouncementLevel::class)],
            'is_active' => ['boolean'],
            'is_dismissible' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ], ['link_url.regex' => 'Links must start with https://, http:// or /.'], ['message.en' => 'English message', 'ends_at' => 'end date']);

        return [
            'message' => Announcement::normalizeTranslations($data['message']),
            'link_url' => $data['link_url'] ?? null,
            'link_label' => Announcement::normalizeTranslations($data['link_label'] ?? []),
            'level' => $data['level'],
            'is_active' => $request->boolean('is_active'),
            'is_dismissible' => $request->boolean('is_dismissible'),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }
}
