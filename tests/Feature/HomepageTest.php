<?php

namespace Tests\Feature;

use App\Enums\CmsContentType;
use App\Enums\HomepageSectionType;
use App\Enums\MenuLocation;
use App\Enums\PlatformModule;
use App\Models\Announcement;
use App\Models\CmsContent;
use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\PlatformSettings;
use Database\Seeders\CmsSeeder;
use Database\Seeders\ContentItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_every_section_managed_in_the_cms(): void
    {
        $this->seed([CmsSeeder::class, ContentItemSeeder::class]);

        $response = $this->get('/')->assertOk()
            ->assertSee('Your everyday Cambodia.')
            ->assertSee('action="'.route('search').'"', false)
            ->assertSee(route('login'))
            ->assertSee(route('register'));

        // The hero heading is split over two lines, so it is checked above instead.
        foreach (HomepageSection::where('type', '!=', HomepageSectionType::Hero)->get() as $section) {
            $response->assertSee($section->translate('title', 'en'), false);
        }

        $response->assertSee('Senior Accountant')
            ->assertSee('Mondulkiri Arabica Coffee 500g')
            ->assertSee('Cambodia Tech Summit')
            ->assertSee('Web Development for Beginners')
            ->assertSee('Hire faster with Cambo Hub Jobs')
            ->assertSee('Sponsored');
    }

    public function test_sections_follow_cms_order_and_hidden_sections_are_left_out(): void
    {
        $this->seed([CmsSeeder::class, ContentItemSeeder::class]);

        HomepageSection::where('type', HomepageSectionType::UpcomingEvents)->update(['sort_order' => -1]);
        HomepageSection::where('type', HomepageSectionType::LatestJobs)->update(['is_visible' => false]);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Featured events', 'Your everyday Cambodia.', 'Latest news'])
            ->assertDontSee('Featured jobs')
            ->assertDontSee('Senior Accountant');
    }

    public function test_turning_a_module_off_removes_its_sections_categories_and_search_filter(): void
    {
        $this->seed([CmsSeeder::class, ContentItemSeeder::class]);
        $settings = app(PlatformSettings::class);
        $settings->update('modules', array_merge($settings->group('modules'), [PlatformModule::Jobs->value => false]));

        $this->get('/')->assertOk()
            ->assertDontSee('Featured jobs')
            ->assertDontSee('Senior Accountant')
            ->assertDontSee('Find your next role')
            ->assertSee('Shop local sellers');

        $this->get(route('search', ['type' => 'job']))->assertSessionHasErrors('type');
        $this->get(route('search', ['q' => 'Accountant']))->assertOk()->assertDontSee('Senior Accountant');
    }

    public function test_only_published_content_within_its_schedule_is_shown(): void
    {
        HomepageSection::factory()->create(['type' => HomepageSectionType::FeaturedNews, 'title' => ['en' => 'Latest news'], 'item_count' => 10]);
        CmsContent::factory()->create(['title' => 'Live headline']);
        CmsContent::factory()->unpublished()->create(['title' => 'Draft headline']);
        CmsContent::factory()->expired()->create(['title' => 'Expired headline']);
        CmsContent::factory()->scheduled()->create(['title' => 'Future headline']);

        $this->get('/')->assertOk()
            ->assertSee('Live headline')
            ->assertDontSee('Draft headline')
            ->assertDontSee('Expired headline')
            ->assertDontSee('Future headline');
    }

    public function test_sections_respect_their_item_count_and_are_skipped_when_empty(): void
    {
        HomepageSection::factory()->create(['type' => HomepageSectionType::MarketplaceHighlights, 'title' => ['en' => 'Popular products'], 'item_count' => 2]);
        HomepageSection::factory()->create(['type' => HomepageSectionType::LatestListings, 'title' => ['en' => 'Property advertisements']]);
        foreach (['Product one', 'Product two', 'Product three'] as $position => $title) {
            CmsContent::factory()->ofType(CmsContentType::Product)->create(['title' => $title, 'sort_order' => $position]);
        }

        $this->get('/')->assertOk()
            ->assertSee('Product one')
            ->assertSee('Product two')
            ->assertDontSee('Product three')
            ->assertDontSee('Property advertisements');
    }

    public function test_branding_menus_footer_and_announcements_come_from_the_cms(): void
    {
        $settings = app(PlatformSettings::class);
        $settings->update('general', array_merge($settings->group('general'), ['site_name' => 'Dendey Khmer', 'tagline' => 'Everything Cambodia']));
        $settings->update('footer', array_merge($settings->group('footer'), ['copyright' => '© {year} Dendey Khmer Co.', 'telegram_url' => 'https://t.me/dendeykhmer']));
        $parent = MenuItem::factory()->create(['location' => MenuLocation::Header, 'label' => ['en' => 'Explore'], 'url' => '#']);
        MenuItem::factory()->create(['location' => MenuLocation::Header, 'parent_id' => $parent->id, 'label' => ['en' => 'Night markets'], 'url' => '/search?q=market']);
        $column = MenuItem::factory()->create(['location' => MenuLocation::Footer, 'label' => ['en' => 'Help'], 'url' => '#']);
        MenuItem::factory()->create(['location' => MenuLocation::Footer, 'parent_id' => $column->id, 'label' => ['en' => 'Contact support'], 'url' => '/contact']);
        Announcement::factory()->create(['message' => ['en' => 'Offices closed for Pchum Ben']]);

        $this->get('/')->assertOk()
            ->assertSee('<title>Home · Dendey Khmer</title>', false)
            ->assertSeeInOrder(['Dendey', 'Khmer', 'Everything Cambodia'])
            ->assertSeeInOrder(['Explore', 'Night markets'])
            ->assertSeeInOrder(['Help', 'Contact support'])
            ->assertSee('© '.now()->year.' Dendey Khmer Co.')
            ->assertSee('https://t.me/dendeykhmer')
            ->assertSee('Offices closed for Pchum Ben');
    }

    public function test_sign_up_links_disappear_while_registration_is_closed(): void
    {
        $this->seed(CmsSeeder::class);
        $settings = app(PlatformSettings::class);
        $settings->update('registration', array_merge($settings->group('registration'), ['enabled' => false]));

        $this->get('/')->assertOk()
            ->assertDontSee(route('register'))
            ->assertSee(route('login'));
    }

    public function test_members_see_dashboard_links_instead_of_registration(): void
    {
        $this->seed([CmsSeeder::class, ContentItemSeeder::class]);

        $this->actingAs(User::factory()->create())->get('/')->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee('Go to your dashboard')
            ->assertDontSee('Create your account')
            ->assertDontSee(route('login'));
    }

    public function test_cms_pages_render_in_the_public_layout(): void
    {
        $this->seed(CmsSeeder::class);

        $this->get('/pages/about-us')->assertOk()
            ->assertSee('<title>About us · Cambo Hub</title>', false)
            ->assertSee('cms-prose', false)
            ->assertSee('Our mission');
    }
}
