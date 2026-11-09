<?php

namespace Tests\Feature;

use App\Enums\CmsContentType;
use App\Models\CmsContent;
use App\Models\CmsSetting;
use App\Models\HomepageSection;
use App\Models\User;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_every_section_supplied_by_the_cms(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get('/')->assertOk()
            ->assertSee('Your everyday Cambodia.')
            ->assertSee('action="'.route('search').'"', false)
            ->assertSee(route('login'))
            ->assertSee(route('register'));

        foreach (config('cms.homepage_sections') as $section) {
            $response->assertSee($section['heading'], false);
        }

        $response->assertSee('Senior Accountant')
            ->assertSee('Mondulkiri Arabica Coffee 500g')
            ->assertSee('Cambodia Tech Summit')
            ->assertSee('Sponsored');
    }

    public function test_sections_follow_cms_order_and_disabled_sections_are_hidden(): void
    {
        $this->seed(CmsSeeder::class);

        HomepageSection::where('content_type', CmsContentType::Event)->update(['sort_order' => 1]);
        HomepageSection::where('content_type', CmsContentType::Job)->update(['is_enabled' => false]);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Featured events', 'Explore our services', 'Latest news'])
            ->assertDontSee('Featured jobs')
            ->assertDontSee('Senior Accountant');
    }

    public function test_only_published_content_within_its_schedule_is_shown(): void
    {
        HomepageSection::create(['content_type' => CmsContentType::News, 'heading' => 'Latest news', 'item_limit' => 10]);
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

    public function test_sections_respect_their_item_limit_and_are_skipped_when_empty(): void
    {
        HomepageSection::create(['content_type' => CmsContentType::Product, 'heading' => 'Popular products', 'item_limit' => 2]);
        HomepageSection::create(['content_type' => CmsContentType::Property, 'heading' => 'Property advertisements']);
        CmsContent::factory()->ofType(CmsContentType::Product)->create(['title' => 'Product one', 'sort_order' => 1]);
        CmsContent::factory()->ofType(CmsContentType::Product)->create(['title' => 'Product two', 'sort_order' => 2]);
        CmsContent::factory()->ofType(CmsContentType::Product)->create(['title' => 'Product three', 'sort_order' => 3]);

        $this->get('/')->assertOk()
            ->assertSee('Product one')
            ->assertSee('Product two')
            ->assertDontSee('Product three')
            ->assertDontSee('Property advertisements');
    }

    public function test_stored_cms_settings_override_branding_navigation_hero_and_footer(): void
    {
        CmsSetting::create(['key' => 'branding', 'value' => ['name' => 'Dendey', 'name_accent' => 'Khmer']]);
        CmsSetting::create(['key' => 'hero', 'value' => ['title' => 'Everything Cambodia, today.']]);
        CmsSetting::create(['key' => 'navigation', 'value' => [['label' => 'Marketplace', 'url' => '/search?type=product']]]);
        CmsSetting::create(['key' => 'footer', 'value' => ['note' => 'Proudly Cambodian.']]);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Dendey', 'Khmer', 'Everything Cambodia, today.'])
            ->assertSee('Everything Cambodia, today.')
            ->assertDontSee('Your everyday Cambodia.')
            ->assertSee('Marketplace')
            ->assertDontSee('Our mission</span>', false)
            ->assertSee('Proudly Cambodian.')
            ->assertSee('Cambo Hub. All rights reserved.');
    }

    public function test_members_see_dashboard_links_instead_of_registration(): void
    {
        $this->seed(CmsSeeder::class);

        $this->actingAs(User::factory()->create())->get('/')->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee('Go to your dashboard')
            ->assertDontSee('Create your account')
            ->assertDontSee(route('login'));
    }
}
