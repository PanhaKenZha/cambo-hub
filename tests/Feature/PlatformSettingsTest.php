<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\PlatformModule;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\PlatformSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_defaults_apply_until_a_value_is_saved(): void
    {
        $settings = app(PlatformSettings::class);

        $this->assertSame('Cambo Hub', $settings->get('general.site_name'));
        $this->assertSame(['en', 'km'], $settings->get('localization.supported_locales'));
        $this->assertFalse($settings->get('maintenance.enabled'));
        $this->assertTrue(PlatformModule::News->isEnabled());
        $this->assertSame('fallback', $settings->get('general.unknown', 'fallback'));
    }

    public function test_every_tab_renders(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->get('/admin/settings')->assertRedirect('/admin/settings/general');

        foreach (array_keys(app(PlatformSettings::class)->groups()) as $group) {
            $this->get("/admin/settings/{$group}")->assertOk()->assertSee('Platform settings');
        }

        $this->get('/admin/settings/modules')->assertOk()->assertSee('Real estate &amp; vehicles', false);
    }

    public function test_saving_settings_stores_typed_values_refreshes_cache_and_is_audited(): void
    {
        $admin = $this->signInAdmin(AdminRole::SuperAdmin);
        $settings = app(PlatformSettings::class);
        $settings->get('general.site_name');

        $this->put('/admin/settings/localization', [
            'default_locale' => 'km',
            'supported_locales' => ['km'],
            'default_currency' => 'KHR',
            'supported_currencies' => ['USD', 'KHR'],
            'khr_per_usd' => '4050',
            'timezone' => 'Asia/Phnom_Penh',
        ])->assertRedirect()->assertSessionHas('status', 'Languages & currency settings saved.');

        $this->assertSame('km', $settings->get('localization.default_locale'));
        $this->assertSame(['km'], $settings->get('localization.supported_locales'));
        $this->assertSame(4050, $settings->get('localization.khr_per_usd'));
        $this->assertSame($admin->id, Setting::where('key', 'localization.khr_per_usd')->value('updated_by'));

        $auditLog = AuditLog::where('action', 'settings.localization.updated')->firstOrFail();
        $this->assertSame(['default_locale' => 'en', 'supported_locales' => ['en', 'km'], 'default_currency' => 'USD', 'khr_per_usd' => 4100], $auditLog->old_values);
        $this->assertSame(4050, $auditLog->new_values['khr_per_usd']);
        $this->assertArrayNotHasKey('timezone', $auditLog->new_values);
    }

    public function test_switches_can_be_turned_off(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->put('/admin/settings/modules', array_merge(
            array_fill_keys(array_column(PlatformModule::cases(), 'value'), '1'),
            ['jobs' => '0'],
        ))->assertRedirect();

        $this->assertFalse(PlatformModule::Jobs->isEnabled());
        $this->assertTrue(PlatformModule::News->isEnabled());
    }

    public function test_saving_unchanged_values_reports_no_changes(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->put('/admin/settings/search', ['enabled' => '1', 'results_per_page' => '20', 'min_query_length' => '2'])
            ->assertSessionHas('status', 'No changes to save.');

        $this->assertDatabaseCount('settings', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->put('/admin/settings/general', ['site_name' => '', 'contact_email' => 'not-an-email'])
            ->assertSessionHasErrors(['site_name', 'contact_email']);
        $this->put('/admin/settings/localization', ['supported_locales' => ['fr'], 'default_locale' => 'fr'])
            ->assertSessionHasErrors(['supported_locales.0', 'default_locale']);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_view_permission_is_read_only_and_edit_permission_is_required_to_save(): void
    {
        $viewer = $this->signInAdmin(AdminRole::NewsAdmin);
        $viewer->givePermissionTo(PermissionArea::Settings->permission(PermissionAction::View));

        $this->get('/admin/settings/general')->assertOk()->assertSee('View only')->assertDontSee('Save general');
        $this->put('/admin/settings/general', ['site_name' => 'Hacked'])->assertForbidden();
        $this->assertSame('Cambo Hub', app(PlatformSettings::class)->get('general.site_name'));
    }

    public function test_admins_without_settings_permission_and_unknown_tabs_are_refused(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $this->get('/admin/settings/general')->assertForbidden();

        $this->signInAdmin(AdminRole::SuperAdmin);
        $this->get('/admin/settings/not-a-tab')->assertNotFound();
        $this->put('/admin/settings/not-a-tab', [])->assertNotFound();
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
