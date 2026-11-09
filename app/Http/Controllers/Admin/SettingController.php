<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\PlatformSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function __construct(private PlatformSettings $settings, private AuditLogger $auditLogger) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.settings.edit', array_key_first($this->settings->groups()));
    }

    public function edit(Request $request, string $group): View
    {
        abort_unless($this->settings->hasGroup($group), Response::HTTP_NOT_FOUND);

        $lastChange = Setting::with('updatedBy')->where('key', 'like', "{$group}.%")->latest('updated_at')->first();

        return view('admin.settings.edit', [
            'groups' => $this->settings->groups(),
            'activeGroup' => $group,
            'definition' => $this->settings->groups()[$group],
            'values' => $this->settings->group($group),
            'canEdit' => $request->user()->can(PermissionArea::Settings->permission(PermissionAction::Edit)),
            'lastChange' => $lastChange,
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless($this->settings->hasGroup($group), Response::HTTP_NOT_FOUND);
        abort_unless($request->user()->can(PermissionArea::Settings->permission(PermissionAction::Edit)), Response::HTTP_FORBIDDEN);

        $data = $request->validate($this->settings->validationRules($group));
        $changes = $this->settings->update($group, $data, $request->user());

        if ($changes['new'] === []) {
            return back()->with('status', 'No changes to save.');
        }

        $this->auditLogger->log("settings.{$group}.updated", null, $changes['old'], $changes['new']);

        return back()->with('status', $this->settings->groups()[$group]['label'].' settings saved.');
    }
}
