<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim Kode OTP');
    }

    public function test_send_otp_creates_record_and_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
            'verified_at' => null,
        ]);

        Mail::assertQueued(PasswordResetOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_send_otp_with_nonexistent_email_shows_generic_message(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nonexistent@example.com'])
            ->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_verify_otp_page_renders(): void
    {
        $this->get(route('password.verify-otp'))
            ->assertOk()
            ->assertSee('Masukkan Kode OTP');
    }

    public function test_verify_otp_with_valid_code_redirects_to_reset(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp' => '123456',
        ])
            ->assertRedirect(route('password.reset', [
                'token' => base64_encode($user->email.'|'.now()->timestamp),
                'email' => $user->email,
            ]));

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
            'verified_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_verify_otp_with_invalid_code_shows_error(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp' => '999999',
        ])
            ->assertSessionHasErrors(['otp']);
    }

    public function test_verify_otp_with_expired_code_shows_error(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => '123456',
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp' => '123456',
        ])
            ->assertSessionHasErrors(['otp']);
    }

    public function test_reset_password_with_verified_otp_succeeds(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $this->post(route('password.update'), [
            'token' => base64_encode($user->email.'|'.now()->timestamp),
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('password_reset_otps', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_without_verified_otp_fails(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('password.update'), [
            'token' => base64_encode($user->email.'|'.now()->timestamp),
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertSessionHasErrors(['email']);
    }
}
