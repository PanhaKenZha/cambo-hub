<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    private function messageData(): array
    {
        return ['name' => 'Dara', 'email' => 'dara@example.com', 'topic' => 'general', 'subject' => 'A question', 'message' => 'I would like to learn more about Cambo Hub.'];
    }

    public function test_contact_page_is_public_and_linked_from_home(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Send us a message')->assertSee('images/backgrounds/customer-support.webp');
        $this->get('/')->assertOk()->assertSee(route('contact'));
    }

    public function test_valid_message_is_saved_and_confirmation_is_shown(): void
    {
        $this->post('/contact', $this->messageData())->assertRedirect('/contact')->assertSessionHas('contact_status');

        $this->assertDatabaseHas('contact_messages', $this->messageData());
        $this->get('/contact')->assertOk()->assertSee('Thank you. Your message has been received.');
    }

    public function test_invalid_message_is_rejected_without_saving(): void
    {
        $this->from('/contact')->post('/contact', ['name' => '', 'email' => 'invalid', 'topic' => 'unknown', 'subject' => '', 'message' => 'short'])
            ->assertRedirect('/contact')->assertSessionHasErrors(['name', 'email', 'topic', 'subject', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_validation_errors_are_linked_to_their_fields(): void
    {
        $this->followingRedirects()->from('/contact')->post('/contact', [...$this->messageData(), 'email' => 'invalid'])
            ->assertOk()
            ->assertSee('Please check the fields below and try again.')
            ->assertSee('href="#email"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="email-error"', false)
            ->assertSee('id="email-error"', false)
            ->assertSee('value="Dara"', false);
    }

    public function test_repeated_submissions_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post('/contact', $this->messageData())->assertRedirect('/contact');
        }
        $this->post('/contact', $this->messageData())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 3);
    }
}
