<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_offer_every_supported_language_in_the_switcher(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (config('app.supported_locales') as $code => $name) {
            $response->assertSee(route('locale.switch', $code))->assertSee($name)
                ->assertSee('images/flags/'.$code.'.svg');
            $this->assertFileExists(public_path('images/flags/'.$code.'.svg'));
        }
    }

    public function test_choosing_a_language_translates_pages_and_returns_to_the_previous_page(): void
    {
        $this->from('/contact')->get('/language/km')->assertRedirect('/contact')->assertSessionHas('locale', 'km');

        $this->get('/contact')->assertOk()
            ->assertSee('<html lang="km">', false)
            ->assertSee('ផ្ញើសារមកយើង')
            ->assertDontSee('Send us a message');

        $this->get('/language/ja');

        $this->get('/mission')->assertOk()->assertSee('私たちのミッション');
    }

    public function test_unsupported_languages_are_rejected(): void
    {
        $this->get('/language/de')->assertNotFound()->assertSessionMissing('locale');

        $this->withSession(['locale' => 'de'])->get('/')->assertOk()->assertSee('<html lang="en">', false);
    }

    public function test_switching_back_to_english_restores_original_content(): void
    {
        $this->withSession(['locale' => 'km'])->from('/contact')->get('/language/en')
            ->assertRedirect('/contact')->assertSessionHas('locale', 'en');

        $this->get('/contact')->assertOk()->assertSee('Send us a message')
            ->assertSee('<html lang="en">', false);
    }

    public function test_switching_language_does_not_redirect_to_external_sites(): void
    {
        $this->withHeaders(['Referer' => 'https://example.org/other-site'])->get('/language/km')
            ->assertRedirect(route('home'))->assertSessionHas('locale', 'km');
    }
}
