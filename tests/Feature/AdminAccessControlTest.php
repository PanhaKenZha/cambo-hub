<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_seeder_creates_every_role_with_its_permissions(): void
    {
        $this->assertSame(count(AdminRole::cases()), Role::count());
        $this->assertTrue(Role::findByName(AdminRole::SuperAdmin->value)->hasPermissionTo(PermissionArea::Administrators->permission(PermissionAction::Manage)));
        $this->assertTrue(Role::findByName(AdminRole::NewsAdmin->value)->hasPermissionTo(PermissionArea::News->permission(PermissionAction::Edit)));
        $this->assertFalse(Role::findByName(AdminRole::NewsAdmin->value)->hasPermissionTo(PermissionArea::Administrators->permission(PermissionAction::Manage)));
        $this->assertFalse(Role::findByName(AdminRole::PlatformAdmin->value)->hasPermissionTo(PermissionArea::Finance->permission(PermissionAction::View)));

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->assertSame(count(AdminRole::cases()), Role::count());
    }

    public function test_admin_without_permission_is_forbidden_from_restricted_modules(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);

        $this->get('/admin')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->post('/admin/users', ['name' => 'X', 'email' => 'x@example.com', 'role' => AdminRole::SuperAdmin->value])->assertForbidden();
        $this->get('/admin/audit-logs')->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'x@example.com']);
    }

    public function test_super_admin_creates_administrator_who_receives_password_link(): void
    {
        Notification::fake();
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);

        $this->post('/admin/users', ['name' => 'Dara News', 'email' => 'dara@example.com', 'role' => AdminRole::NewsAdmin->value])
            ->assertRedirect();

        $newAdmin = User::where('email', 'dara@example.com')->firstOrFail();
        $this->assertTrue($newAdmin->hasRole(AdminRole::NewsAdmin->value));
        Notification::assertSentTo($newAdmin, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $superAdmin->id, 'action' => 'admin.created', 'auditable_id' => $newAdmin->id]);
    }

    public function test_only_super_admin_can_grant_or_manage_super_admin(): void
    {
        $manager = User::factory()->withTwoFactor()->admin(AdminRole::PlatformAdmin)->create();
        $manager->givePermissionTo(PermissionArea::Administrators->permissions());
        $superAdmin = User::factory()->admin(AdminRole::SuperAdmin)->create();
        $this->actingAs($manager)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        $this->post('/admin/users', ['name' => 'Sneaky', 'email' => 'sneaky@example.com', 'role' => AdminRole::SuperAdmin->value])
            ->assertSessionHasErrors('role');
        $this->get("/admin/users/{$superAdmin->id}/edit")->assertForbidden();
        $this->post("/admin/users/{$superAdmin->id}/suspend", ['reason' => 'test'])->assertForbidden();
        $this->assertNull($superAdmin->refresh()->suspended_at);
    }

    public function test_admin_cannot_suspend_themselves_or_change_own_role(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);

        $this->post("/admin/users/{$superAdmin->id}/suspend", ['reason' => 'test'])->assertForbidden();
        $this->put("/admin/users/{$superAdmin->id}", [
            'name' => $superAdmin->name, 'email' => $superAdmin->email, 'role' => AdminRole::SupportAgent->value,
        ])->assertSessionHasErrors('role');

        $this->assertTrue($superAdmin->refresh()->isSuperAdmin());
        $this->assertFalse($superAdmin->isSuspended());
    }

    public function test_dashboard_shows_security_figures_and_activity_only_to_permitted_roles(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);
        User::factory()->admin(AdminRole::NewsAdmin)->create();
        app(AuditLogger::class)->log('admin.created', $superAdmin);

        $this->get('/admin')->assertOk()
            ->assertSee('Registered users')
            ->assertSee('Admins without 2FA')
            ->assertSee('Admin created');

        $this->signInAdmin(AdminRole::NewsAdmin);

        $this->get('/admin')->assertOk()
            ->assertSee('Registered users')
            ->assertDontSee('Admins without 2FA')
            ->assertDontSee('Admin created')
            ->assertSee('does not include access to the audit log');
    }

    public function test_admin_management_pages_render_for_super_admin(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $target = User::factory()->admin(AdminRole::HostingAdmin)->suspended('Investigation')->create(['name' => 'Sokha Hosting']);
        $target->loginHistories()->create(['email' => $target->email, 'ip_address' => '203.0.113.5', 'successful' => false, 'failure_reason' => 'invalid_credentials']);

        $this->get('/admin/users?status=suspended')->assertOk()->assertSee('Sokha Hosting');
        $this->get('/admin/users/create')->assertOk()->assertSee('Add administrator');
        $this->get("/admin/users/{$target->id}")->assertOk()->assertSee('Investigation')->assertSee('203.0.113.5');
        $this->get("/admin/users/{$target->id}/edit")->assertOk()->assertSee('Edit administrator');
    }

    public function test_non_admin_users_cannot_be_managed_through_admin_screens(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $member = User::factory()->create();

        $this->get("/admin/users/{$member->id}")->assertForbidden();
        $this->post("/admin/users/{$member->id}/suspend", ['reason' => 'test'])->assertForbidden();
    }

    public function test_suspending_and_reactivating_admin_is_audited_and_blocks_access(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);
        $target = User::factory()->withTwoFactor()->admin(AdminRole::JobsAdmin)->create();

        $this->post("/admin/users/{$target->id}/suspend", ['reason' => 'Shared credentials'])->assertRedirect();
        $this->assertTrue($target->refresh()->isSuspended());
        $this->assertSame('Shared credentials', $target->suspension_reason);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $superAdmin->id, 'action' => 'admin.suspended', 'auditable_id' => $target->id]);

        $this->post("/admin/users/{$target->id}/unsuspend")->assertRedirect();
        $this->assertFalse($target->refresh()->isSuspended());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.unsuspended', 'auditable_id' => $target->id]);
    }

    public function test_updating_admin_records_old_and_new_values(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $target = User::factory()->admin(AdminRole::JobsAdmin)->create(['name' => 'Old Name']);

        $this->put("/admin/users/{$target->id}", [
            'name' => 'New Name', 'email' => $target->email, 'role' => AdminRole::MarketplaceAdmin->value,
        ])->assertRedirect("/admin/users/{$target->id}");

        $target->refresh();
        $this->assertSame('New Name', $target->name);
        $this->assertSame(AdminRole::MarketplaceAdmin, $target->adminRole());
        $auditLog = AuditLog::where('action', 'admin.updated')->firstOrFail();
        $this->assertSame(['name' => 'Old Name', 'email' => $target->email, 'role' => AdminRole::JobsAdmin->value], $auditLog->old_values);
        $this->assertSame(AdminRole::MarketplaceAdmin->value, $auditLog->new_values['role']);
    }

    public function test_resetting_two_factor_forces_reenrollment(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $target = User::factory()->withTwoFactor()->admin(AdminRole::FinanceAdmin)->create();

        $this->post("/admin/users/{$target->id}/reset-two-factor")->assertRedirect();

        $this->assertFalse($target->refresh()->hasTwoFactorEnabled());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.two_factor_reset', 'auditable_id' => $target->id]);
    }

    public function test_audit_log_page_lists_entries_and_never_stores_secrets(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);
        app(AuditLogger::class)->log('admin.updated', $superAdmin, [], ['name' => 'Visible', 'password' => 'secret-value', 'two_factor_secret' => 'ABC']);

        $auditLog = AuditLog::firstOrFail();
        $this->assertSame(['name' => 'Visible'], $auditLog->new_values);
        $this->get('/admin/audit-logs')->assertOk()->assertSee('admin.updated')->assertDontSee('secret-value');
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
