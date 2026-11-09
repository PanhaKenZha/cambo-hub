<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\BannerPlacement;
use App\Enums\HomepageSectionType;
use App\Enums\MenuLocation;
use App\Enums\PlatformModule;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use App\Services\PlatformSettings;
use App\Services\SiteContent;
use Database\Seeders\CmsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsSiteStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->signInAdmin();
    }

    public function test_menu_items_link_to_pages_or_urls_with_one_level_of_children(): void
    {
        $page = Page::factory()->create(['slug' => 'about-us']);

        $this->post('/admin/cms/menus', ['location' => 'header', 'label' => ['en' => 'Company', 'km' => 'ក្រុមហ៊ុន'], 'link_type' => 'url', 'url' => '/company', 'is_visible' => '1'])->assertRedirect();
        $parent = MenuItem::firstOrFail();
        $this->post('/admin/cms/menus', ['location' => 'header', 'parent_id' => $parent->id, 'label' => ['en' => 'About'], 'link_type' => 'page', 'page_id' => $page->id, 'is_visible' => '1'])->assertRedirect();
        $this->post('/admin/cms/menus', ['location' => 'header', 'label' => ['en' => 'Hidden'], 'link_type' => 'url', 'url' => '/hidden', 'is_visible' => '0']);
        $this->post('/admin/cms/menus', ['location' => 'header', 'label' => ['en' => 'Bad'], 'link_type' => 'url', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');

        $child = MenuItem::where('page_id', $page->id)->firstOrFail();
        $this->put("/admin/cms/menus/{$parent->id}", ['location' => 'header', 'parent_id' => $child->id, 'label' => ['en' => 'Company'], 'link_type' => 'url', 'url' => '/company'])
            ->assertSessionHasErrors('parent_id');

        $menu = app(SiteContent::class)->menu(MenuLocation::Header, 'km');
        $this->assertCount(1, $menu);
        $this->assertSame('ក្រុមហ៊ុន', $menu[0]['label']);
        $this->assertSame(route('pages.show', 'about-us'), $menu[0]['children'][0]['url']);
        $this->get('/admin/cms/menus?location=header')->assertOk()->assertSee('Company');
    }

    public function test_items_can_be_reordered(): void
    {
        $first = MenuItem::factory()->create(['label' => ['en' => 'First'], 'sort_order' => 0]);
        MenuItem::factory()->create(['label' => ['en' => 'Second'], 'sort_order' => 1]);

        $this->post("/admin/cms/menus/{$first->id}/move", ['direction' => 'down'])->assertRedirect();

        $this->assertSame(['Second', 'First'], array_column(app(SiteContent::class)->menu(MenuLocation::Header), 'label'));
    }

    public function test_banners_need_an_image_and_respect_their_schedule(): void
    {
        Storage::fake('public');

        $this->post('/admin/cms/banners', ['placement' => 'homepage_hero', 'title' => ['en' => 'No image']])->assertSessionHasErrors('image');
        $this->post('/admin/cms/banners', [
            'placement' => 'homepage_hero',
            'title' => ['en' => 'Khmer New Year sale'],
            'image' => UploadedFile::fake()->image('banner.jpg', 1920, 640),
            'is_active' => '1',
            'starts_at' => now()->addDay()->format('Y-m-d H:i'),
        ])->assertRedirect('/admin/cms/banners');

        $banner = Banner::firstOrFail();
        Storage::disk('public')->assertExists($banner->image_path);
        $this->assertSame([], app(SiteContent::class)->banners(BannerPlacement::HomepageHero));

        $this->travel(2)->days();
        $this->assertSame('Khmer New Year sale', app(SiteContent::class)->banners(BannerPlacement::HomepageHero)[0]['title']);
        $this->travelBack();

        $this->get('/admin/cms/banners')->assertOk()->assertSee('Khmer New Year sale')->assertSee('Scheduled');
        $this->get('/admin/cms/banners?placement=sidebar')->assertOk()->assertDontSee('Khmer New Year sale');

        $this->delete("/admin/cms/banners/{$banner->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($banner->image_path);
    }

    public function test_banner_uploads_follow_upload_settings(): void
    {
        Storage::fake('public');
        $settings = app(PlatformSettings::class);
        $settings->update('uploads', array_merge($settings->group('uploads'), ['max_size_mb' => 1, 'image_types' => ['png']]));

        $this->post('/admin/cms/banners', ['placement' => 'sidebar', 'title' => ['en' => 'JPG'], 'image' => UploadedFile::fake()->image('a.jpg')])->assertSessionHasErrors('image');
        $this->post('/admin/cms/banners', ['placement' => 'sidebar', 'title' => ['en' => 'Huge'], 'image' => UploadedFile::fake()->image('a.png')->size(2048)])->assertSessionHasErrors('image');
    }

    public function test_homepage_sections_hide_when_hidden_or_module_is_off(): void
    {
        $this->seed(CmsSeeder::class);
        $jobs = HomepageSection::where('type', HomepageSectionType::LatestJobs)->firstOrFail();
        $news = HomepageSection::where('type', HomepageSectionType::FeaturedNews)->firstOrFail();

        $this->assertContains('latest_jobs', array_column(app(SiteContent::class)->homepageSections(), 'type'));

        $settings = app(PlatformSettings::class);
        $settings->update('modules', array_merge($settings->group('modules'), [PlatformModule::Jobs->value => false]));
        $this->post("/admin/cms/homepage/{$news->id}/toggle")->assertRedirect();

        $types = array_column(app(SiteContent::class)->homepageSections(), 'type');
        $this->assertNotContains('latest_jobs', $types);
        $this->assertNotContains('featured_news', $types);
        $this->get('/admin/cms/homepage')->assertOk()->assertSee('Module off')->assertSee('Jobs is turned off');

        $this->put("/admin/cms/homepage/{$jobs->id}", ['title' => ['en' => 'Hiring now'], 'item_count' => '12', 'is_visible' => '1'])->assertRedirect();
        $this->assertSame(12, $jobs->fresh()->item_count);
    }

    public function test_promo_block_can_be_added_and_reordered_to_the_top(): void
    {
        $this->seed(CmsSeeder::class);

        $this->post('/admin/cms/homepage', ['type' => 'promo_block', 'title' => ['en' => 'Advertise with us'], 'link_url' => '/pages/contact', 'is_visible' => '1'])->assertRedirect();
        $promo = HomepageSection::where('type', HomepageSectionType::PromoBlock)->firstOrFail();

        foreach (range(1, HomepageSection::count()) as $step) {
            $this->post("/admin/cms/homepage/{$promo->id}/move", ['direction' => 'up']);
        }

        $this->assertSame('promo_block', app(SiteContent::class)->homepageSections()[0]['type']);
    }

    public function test_announcements_run_between_their_dates(): void
    {
        $this->post('/admin/cms/announcements', [
            'message' => ['en' => 'Offices closed for Pchum Ben', 'km' => 'ការិយាល័យបិទ'],
            'level' => 'warning',
            'is_active' => '1',
            'is_dismissible' => '1',
            'ends_at' => now()->addDays(3)->format('Y-m-d H:i'),
        ])->assertRedirect('/admin/cms/announcements');
        Announcement::factory()->create(['is_active' => false, 'message' => ['en' => 'Switched off']]);

        $announcements = app(SiteContent::class)->announcements('km');
        $this->assertCount(1, $announcements);
        $this->assertSame('ការិយាល័យបិទ', $announcements[0]['message']);
        $this->assertSame('warning', $announcements[0]['level']);

        $this->travel(4)->days();
        $this->assertSame([], app(SiteContent::class)->announcements());
    }

    public function test_footer_combines_settings_and_footer_menu(): void
    {
        $this->seed(CmsSeeder::class);

        $footer = app(SiteContent::class)->footer();

        $this->assertStringContainsString((string) now()->year, $footer['copyright']);
        $this->assertSame(['Company', 'Legal'], array_column($footer['menu'], 'label'));
        $this->get('/admin/settings/footer')->assertOk()->assertSee('Copyright line');
    }

    public function test_seeder_only_fills_empty_areas(): void
    {
        $this->seed(CmsSeeder::class);
        HomepageSection::first()->update(['title' => ['en' => 'Custom hero']]);

        $this->seed(CmsSeeder::class);

        $this->assertSame(8, HomepageSection::count());
        $this->assertSame(4, Page::count());
        $this->assertSame('Custom hero', HomepageSection::first()->translate('title', 'en'));
    }

    private function signInAdmin(AdminRole $role = AdminRole::SuperAdmin): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
