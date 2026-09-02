<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/registro', [
            'name' => 'Usuario Test',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'nuevo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifactuEnabled());
        $this->assertSame(\App\Models\UserSifConfig::MODE_VERIFACTU, $user->sifConfig->mode);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->onboarded()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->onboarded()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_verify_email(): void
    {
        $user = User::factory()->unverified()->create();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($url)->assertRedirect();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_login_and_register_show_google_button(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Continuar con Google')
            ->assertSee('o con email');
        $this->get('/registro')
            ->assertOk()
            ->assertSee('Continuar con Google')
            ->assertSee('o con email');
    }

    public function test_google_redirect_requires_configuration(): void
    {
        $this->from('/registro')->get('/auth/google')
            ->assertRedirect('/registro')
            ->assertSessionHas('error');
    }

    public function test_google_callback_creates_verified_user(): void
    {
        $this->enableGoogleAuth();
        $this->mockGoogleUser();
        \Illuminate\Support\Facades\Mail::fake();

        $this->get('/auth/google/callback')
            ->assertRedirect(route('onboarding.step', ['step' => 1]));

        $this->assertAuthenticated();
        $user = User::where('email', 'ada@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-abc', $user->google_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasVerifactuEnabled());
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\WelcomeMail::class);
        $this->assertDatabaseHas('analytics_events', ['name' => \App\Models\AnalyticsEvent::REGISTRATION_COMPLETED]);
    }

    public function test_google_callback_logs_in_existing_user_by_email(): void
    {
        $this->enableGoogleAuth();
        $this->mockGoogleUser();
        $user = User::factory()->onboarded()->create([
            'email' => 'ada@gmail.com',
            'name' => 'Ada',
        ]);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-abc', $user->fresh()->google_id);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    private function enableGoogleAuth(): void
    {
        config([
            'services.google.client_id' => 'test-google-id',
            'services.google.client_secret' => 'test-google-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    private function mockGoogleUser(array $overrides = []): void
    {
        $googleUser = (new \Laravel\Socialite\Two\User)->map(array_merge([
            'id' => 'google-abc',
            'name' => 'Ada Lovelace',
            'email' => 'ada@gmail.com',
        ], $overrides));

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        $provider->shouldReceive('user')->andReturn($googleUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
