<?php

namespace Tests\Feature;

use App\Enums\CmsContentType;
use App\Models\CmsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_published_content_across_services(): void
    {
        CmsContent::factory()->ofType(CmsContentType::Job)->create(['title' => 'Coffee shop manager']);
        CmsContent::factory()->ofType(CmsContentType::Product)->create(['title' => 'Arabica beans', 'summary' => 'Fresh roasted coffee']);
        CmsContent::factory()->ofType(CmsContentType::Business)->create(['title' => 'Unrelated bakery']);
        CmsContent::factory()->ofType(CmsContentType::News)->unpublished()->create(['title' => 'Coffee draft']);
        CmsContent::factory()->ofType(CmsContentType::Advertisement)->create(['title' => 'Coffee advert']);

        $this->get(route('search', ['q' => 'coffee']))->assertOk()
            ->assertSee('Coffee shop manager')
            ->assertSee('Arabica beans')
            ->assertDontSee('Unrelated bakery')
            ->assertDontSee('Coffee draft')
            ->assertDontSee('Coffee advert');
    }

    public function test_search_can_be_limited_to_one_service(): void
    {
        CmsContent::factory()->ofType(CmsContentType::Job)->create(['title' => 'Phnom Penh accountant']);
        CmsContent::factory()->ofType(CmsContentType::Property)->create(['title' => 'Phnom Penh condo']);

        $this->get(route('search', ['q' => 'Phnom Penh', 'type' => 'property']))->assertOk()
            ->assertSee('Phnom Penh condo')
            ->assertDontSee('Phnom Penh accountant');
    }

    public function test_wildcard_characters_are_matched_literally(): void
    {
        CmsContent::factory()->create(['title' => 'Sale 50% off']);
        CmsContent::factory()->create(['title' => 'Regular price']);

        $this->get(route('search', ['q' => '0%']))->assertOk()
            ->assertSee('Sale 50% off')
            ->assertDontSee('Regular price');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->get(route('search', ['type' => 'advertisement']))->assertSessionHasErrors('type');
        $this->get(route('search', ['q' => str_repeat('a', 101)]))->assertSessionHasErrors('q');
    }

    public function test_empty_search_prompts_for_a_keyword(): void
    {
        $this->get(route('search'))->assertOk()->assertSee('What are you looking for?');
    }
}
