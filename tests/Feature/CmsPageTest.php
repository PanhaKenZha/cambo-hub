<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\Page;
use App\Models\User;
use App\Services\SiteContent;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_creates_bilingual_page_that_is_served_publicly(): void
    {
        $admin = $this->signInAdmin(AdminRole::PlatformAdmin);

        $this->get('/admin/cms/pages/create')->assertOk()->assertSee('Page text');
        $response = $this->post('/admin/cms/pages', [
            'title' => ['en' => 'About Cambo Hub', 'km' => 'អំពី Cambo Hub'],
            'body' => ['en' => "## Who we are\n\nA **platform** for Cambodia.", 'km' => 'វេទិកាសម្រាប់កម្ពុជា'],
            'status' => 'published',
        ]);

        $page = Page::where('slug', 'about-cambo-hub')->firstOrFail();
        $response->assertRedirect("/admin/cms/pages/{$page->id}/edit");
        $this->assertSame($admin->id, $page->created_by);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.page.created', 'auditable_id' => $page->id]);

        $this->get('/pages/about-cambo-hub')->assertOk()->assertSee('About Cambo Hub')->assertSee('<strong>platform</strong>', false);
        $this->assertSame('អំពី Cambo Hub', app(SiteContent::class)->page('about-cambo-hub', 'km')['title']);
    }

    public function test_khmer_falls_back_to_english_when_empty(): void
    {
        $page = Page::factory()->create(['slug' => 'faq', 'title' => ['en' => 'FAQ', 'km' => null]]);

        $this->assertSame('FAQ', $page->translate('title', 'km'));
        $this->assertSame('FAQ', app(SiteContent::class)->page('faq', 'km')['title']);
    }

    public function test_markdown_cannot_inject_html_or_scripts(): void
    {
        $page = Page::factory()->create(['body' => ['en' => "Hello <script>alert('x')</script>\n\n[click](javascript:alert(1))", 'km' => null]]);

        $html = $page->bodyHtml('en');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_drafts_scheduled_and_expired_pages_are_not_public_but_can_be_previewed(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $draft = Page::factory()->draft()->create(['slug' => 'draft-page']);
        Page::factory()->create(['slug' => 'scheduled-page', 'published_at' => now()->addDay()]);
        Page::factory()->create(['slug' => 'expired-page', 'unpublish_at' => now()->subHour()]);

        $this->get('/pages/draft-page')->assertNotFound();
        $this->get('/pages/scheduled-page')->assertNotFound();
        $this->get('/pages/expired-page')->assertNotFound();
        $this->get("/admin/cms/pages/{$draft->id}/preview")->assertOk()->assertSee('Preview · Draft');

        $this->travel(2)->days();
        $this->get('/pages/scheduled-page')->assertOk();
    }

    public function test_page_cache_refreshes_when_page_is_saved(): void
    {
        $page = Page::factory()->create(['slug' => 'pricing', 'title' => ['en' => 'Old title', 'km' => null]]);
        $this->assertSame('Old title', app(SiteContent::class)->page('pricing')['title']);

        $page->update(['title' => ['en' => 'New title', 'km' => null]]);

        $this->assertSame('New title', app(SiteContent::class)->page('pricing')['title']);
    }

    public function test_editors_without_publish_permission_can_only_save_drafts(): void
    {
        $editor = $this->signInAdmin(AdminRole::NewsAdmin);
        $editor->givePermissionTo([
            PermissionArea::WebsiteContent->permission(PermissionAction::View),
            PermissionArea::WebsiteContent->permission(PermissionAction::Create),
            PermissionArea::WebsiteContent->permission(PermissionAction::Edit),
        ]);

        $this->post('/admin/cms/pages', ['title' => ['en' => 'Sneaky'], 'status' => 'published'])->assertSessionHasErrors('status');
        $this->post('/admin/cms/pages', ['title' => ['en' => 'My draft'], 'status' => 'draft'])->assertRedirect();

        $published = Page::factory()->create(['slug' => 'live']);
        $this->put("/admin/cms/pages/{$published->id}", ['title' => ['en' => 'Typo fixed'], 'slug' => 'live', 'status' => 'published'])->assertSessionHasNoErrors();
        $this->put("/admin/cms/pages/{$published->id}", ['title' => ['en' => 'Typo fixed'], 'slug' => 'live', 'status' => 'draft'])->assertSessionHasErrors('status');

        $this->assertSame(ContentStatus::Published, $published->fresh()->status);
        $this->assertSame('Typo fixed', $published->fresh()->translate('title', 'en'));
        $this->delete("/admin/cms/pages/{$published->id}")->assertForbidden();
    }

    public function test_slug_must_be_unique_and_admins_without_cms_access_are_refused(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        Page::factory()->create(['slug' => 'contact']);

        $this->post('/admin/cms/pages', ['title' => ['en' => 'Contact'], 'status' => 'draft'])->assertSessionHasErrors('slug');
        $this->get('/admin/cms/pages')->assertOk()->assertSee('/pages/contact');

        $this->signInAdmin(AdminRole::FinanceAdmin);
        $this->get('/admin/cms/pages')->assertForbidden();
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
