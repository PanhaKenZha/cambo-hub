<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_role_list_shows_every_role_with_its_access_level_per_area(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->get('/admin/roles')->assertOk()
            ->assertSee('Support Agent')
            ->assertSee('Built-in')
            ->assertSee('Real estate &amp; vehicles', false)
            ->assertSee('Full')
            ->assertSee(route('admin.roles.edit', $this->role(AdminRole::NewsAdmin)), false)
            ->assertDontSee(route('admin.roles.edit', $this->role(AdminRole::SuperAdmin)), false);
    }

    public function test_access_level_is_full_manage_view_or_none(): void
    {
        $role = Role::create(['name' => 'Levels', 'guard_name' => 'web']);
        $role->givePermissionTo([
            ...PermissionArea::News->permissions(),
            PermissionArea::Jobs->permission(PermissionAction::View),
            PermissionArea::Jobs->permission(PermissionAction::Edit),
            PermissionArea::Hosting->permission(PermissionAction::View),
        ]);
        $role->load('permissions');

        $this->assertSame('full', $role->accessLevel(PermissionArea::News));
        $this->assertSame('manage', $role->accessLevel(PermissionArea::Jobs));
        $this->assertSame('view', $role->accessLevel(PermissionArea::Hosting));
        $this->assertSame('none', $role->accessLevel(PermissionArea::Finance));
    }

    public function test_super_admin_creates_custom_role_that_can_be_assigned(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);

        $this->get('/admin/roles/create')->assertOk()->assertSee('Role information');
        $this->post('/admin/roles', [
            'name' => 'Content Editor',
            'description' => 'Writes news',
            'permissions' => [PermissionArea::Dashboard->permission(PermissionAction::View), PermissionArea::News->permission(PermissionAction::Edit)],
        ])->assertRedirect('/admin/roles');

        $role = Role::findByName('Content Editor');
        $this->assertFalse($role->isSystem());
        $this->assertSame('manage', $role->accessLevel(PermissionArea::News));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $superAdmin->id, 'action' => 'role.created', 'auditable_id' => $role->id]);

        $this->post('/admin/users', ['name' => 'Editor', 'email' => 'editor@example.com', 'role' => 'Content Editor'])->assertRedirect();
        $editor = User::where('email', 'editor@example.com')->firstOrFail();
        $this->assertTrue($editor->isAdmin());
        $this->assertSame('Content Editor', $editor->roleLabel());
    }

    public function test_role_names_must_be_unique_and_not_reserved(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->post('/admin/roles', ['name' => AdminRole::NewsAdmin->value])->assertSessionHasErrors('name');
        $this->post('/admin/roles', ['name' => 'Reviewer']);
        $this->post('/admin/roles', ['name' => 'Reviewer'])->assertSessionHasErrors('name');
        $this->post('/admin/roles', ['name' => 'Other', 'permissions' => ['everything.delete']])->assertSessionHasErrors('permissions.0');
    }

    public function test_granting_and_revoking_permissions_changes_access_immediately_and_is_audited(): void
    {
        $superAdmin = $this->signInAdmin(AdminRole::SuperAdmin);
        $newsAdmin = User::factory()->withTwoFactor()->admin(AdminRole::NewsAdmin)->create();
        $newsRole = $this->role(AdminRole::NewsAdmin);
        $auditLogView = PermissionArea::AuditLogs->permission(PermissionAction::View);

        $this->put("/admin/roles/{$newsRole->id}", ['permissions' => [...$newsRole->permissionNames(), $auditLogView]])
            ->assertRedirect('/admin/roles');

        $auditLog = AuditLog::where('action', 'role.updated')->firstOrFail();
        $this->assertSame($superAdmin->id, $auditLog->user_id);
        $this->assertNotContains($auditLogView, $auditLog->old_values['permissions']);
        $this->assertContains($auditLogView, $auditLog->new_values['permissions']);

        $this->actingAs($newsAdmin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);
        $this->get('/admin/audit-logs')->assertOk();

        $this->actingAs($superAdmin)->put("/admin/roles/{$newsRole->id}", ['permissions' => [PermissionArea::Dashboard->permission(PermissionAction::View)]]);

        $this->actingAs($newsAdmin)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_built_in_roles_cannot_be_renamed_or_deleted(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $role = $this->role(AdminRole::JobsAdmin);

        $this->put("/admin/roles/{$role->id}", ['name' => 'Renamed', 'permissions' => []])->assertSessionHasErrors('name');
        $this->delete("/admin/roles/{$role->id}")->assertForbidden();
        $this->assertSame(AdminRole::JobsAdmin->value, $role->fresh()->name);
    }

    public function test_custom_role_can_be_renamed_and_deleted_only_when_unused(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $role = Role::create(['name' => 'Temporary', 'guard_name' => 'web']);

        $this->put("/admin/roles/{$role->id}", ['name' => 'Seasonal', 'permissions' => []])->assertRedirect('/admin/roles');
        $this->assertSame('Seasonal', $role->fresh()->name);

        $holder = User::factory()->create();
        $holder->assignRole('Seasonal');
        $this->delete("/admin/roles/{$role->id}")->assertForbidden();

        $holder->removeRole('Seasonal');
        $this->delete("/admin/roles/{$role->id}")->assertRedirect('/admin/roles');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.deleted']);
    }

    public function test_reset_restores_default_permissions(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $role = $this->role(AdminRole::JobsAdmin);
        $this->put("/admin/roles/{$role->id}", ['permissions' => [PermissionArea::Dashboard->permission(PermissionAction::View)]]);

        $this->post("/admin/roles/{$role->id}/reset")->assertRedirect("/admin/roles/{$role->id}/edit");

        $this->assertEqualsCanonicalizing(AdminRole::JobsAdmin->defaultPermissions(), $role->fresh()->permissionNames());
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.permissions_reset']);
    }

    public function test_super_admin_role_and_own_role_cannot_be_edited(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $superAdminRole = $this->role(AdminRole::SuperAdmin);

        $this->get("/admin/roles/{$superAdminRole->id}/edit")->assertForbidden();
        $this->put("/admin/roles/{$superAdminRole->id}", ['permissions' => []])->assertForbidden();
        $this->assertCount(count(PermissionArea::allPermissions()), $superAdminRole->fresh()->permissions);

        $platformAdmin = $this->signInAdmin(AdminRole::PlatformAdmin);
        $platformAdmin->givePermissionTo(PermissionArea::Roles->permissions());
        $this->get("/admin/roles/{$this->role(AdminRole::PlatformAdmin)->id}/edit")->assertForbidden();
    }

    public function test_admins_can_only_grant_permissions_they_hold(): void
    {
        $platformAdmin = $this->signInAdmin(AdminRole::PlatformAdmin);
        $platformAdmin->givePermissionTo(PermissionArea::Roles->permissions());
        $supportRole = $this->role(AdminRole::SupportAgent);
        $financeEdit = PermissionArea::Finance->permission(PermissionAction::Edit);
        $newsView = PermissionArea::News->permission(PermissionAction::View);

        $this->put("/admin/roles/{$supportRole->id}", ['permissions' => [$financeEdit, $newsView]])->assertRedirect('/admin/roles');

        $permissions = $supportRole->fresh()->permissionNames();
        $this->assertContains($newsView, $permissions);
        $this->assertNotContains($financeEdit, $permissions);
        $this->assertNotContains(PermissionArea::Dashboard->permission(PermissionAction::View), $permissions);
    }

    public function test_view_only_access_and_no_access(): void
    {
        $viewer = $this->signInAdmin(AdminRole::PlatformAdmin);
        $viewer->givePermissionTo(PermissionArea::Roles->permission(PermissionAction::View));

        $this->get('/admin/roles')->assertOk()->assertSee('You can view roles but not change them');
        $this->get("/admin/roles/{$this->role(AdminRole::NewsAdmin)->id}/edit")->assertForbidden();
        $this->post('/admin/roles', ['name' => 'Nope'])->assertForbidden();

        $this->signInAdmin(AdminRole::NewsAdmin);
        $this->get('/admin/roles')->assertForbidden();
    }

    public function test_reseeding_keeps_customised_permissions(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $hostingRole = $this->role(AdminRole::HostingAdmin);
        $hostingView = PermissionArea::Hosting->permission(PermissionAction::View);
        $this->put("/admin/roles/{$hostingRole->id}", ['permissions' => [$hostingView]]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame([$hostingView], $hostingRole->fresh()->permissionNames());
        $this->assertCount(count(PermissionArea::allPermissions()), $this->role(AdminRole::SuperAdmin)->permissions);
    }

    private function role(AdminRole $adminRole): Role
    {
        return Role::findByName($adminRole->value);
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
