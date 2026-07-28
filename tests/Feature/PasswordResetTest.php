<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/password/olvidada', [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post('/password/restablecer/'.$token, [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
