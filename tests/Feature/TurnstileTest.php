<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    private function configureTurnstile(): void
    {
        config([
            'turnstile.site_key' => 'test-site-key',
            'turnstile.secret_key' => 'test-secret-key',
        ]);
    }

    public function test_login_works_when_turnstile_is_not_configured(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_page_shows_widget_when_site_key_is_configured(): void
    {
        $this->configureTurnstile();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('cf-turnstile');
        $response->assertSee('test-site-key');
    }

    public function test_widget_is_hidden_when_site_key_is_missing(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('cf-turnstile', false);
    }

    public function test_login_fails_when_turnstile_verification_fails(): void
    {
        $this->configureTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
    }

    public function test_login_succeeds_when_turnstile_verification_passes(): void
    {
        $this->configureTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'good-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_register_fails_when_turnstile_verification_fails(): void
    {
        $this->configureTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false]),
        ]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'turnstile-test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => 'turnstile-test@example.com']);
    }

    public function test_forgot_password_fails_when_turnstile_verification_fails(): void
    {
        $this->configureTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false]),
        ]);

        $this->post('/forgot-password', [
            'email' => 'someone@example.com',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_reset_password_fails_when_turnstile_verification_fails(): void
    {
        $this->configureTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false]),
        ]);

        $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => 'someone@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_verification_is_skipped_without_secret_key_even_if_widget_shows(): void
    {
        config(['turnstile.site_key' => 'test-site-key']);
        Http::fake();

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        Http::assertNothingSent();
    }
}
