<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset your password')
            ->assertSee('Send Reset Link');
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->post(route('password.email'), [
            'email' => 'owner@example.com',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'If your email exists in our system, you will receive a password reset link.');

        Notification::assertSentTo($user, AdminResetPasswordNotification::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'owner@example.com']);
    }

    public function test_system_does_not_reveal_if_email_exists(): void
    {
        Notification::fake();

        $this->post(route('password.email'), [
            'email' => 'missing@example.com',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'If your email exists in our system, you will receive a password reset link.');

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'missing@example.com']);
    }

    public function test_reset_password_page_loads_with_token(): void
    {
        $this->get(route('password.reset', [
            'token' => 'test-token',
            'email' => 'owner@example.com',
        ]))
            ->assertOk()
            ->assertSee('Create new password')
            ->assertSee('Reset Password');
    }

    public function test_user_can_reset_password_with_valid_token_and_login(): void
    {
        $user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('old-password'),
            'role' => 'manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'manager@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset successfully. You can now log in.');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'manager@example.com']);

        $this->post(route('login.submit'), [
            'login' => 'manager@example.com',
            'password' => 'new-password-123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_token_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('old-password'),
            'role' => 'admin',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'admin@example.com',
            'password' => 'first-password-123',
            'password_confirmation' => 'first-password-123',
        ])->assertRedirect(route('login'));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'admin@example.com',
            'password' => 'second-password-123',
            'password_confirmation' => 'second-password-123',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('first-password-123', $user->password));
        $this->assertFalse(Hash::check('second-password-123', $user->password));
    }

    public function test_invalid_or_expired_token_shows_error(): void
    {
        $user = User::factory()->create([
            'email' => 'expired@example.com',
            'password' => Hash::make('old-password'),
        ]);
        $token = Password::broker()->createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', 'expired@example.com')
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'expired@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
