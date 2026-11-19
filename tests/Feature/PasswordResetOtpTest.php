<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_page_places_the_visibility_toggle_inside_the_field(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'token-reset',
            'email' => 'test@example.com',
        ]));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(2, substr_count($html, 'class="password-field"'));
        $this->assertSame(2, substr_count($html, 'class="password-toggle"'));
        $this->assertMatchesRegularExpression(
            '/<div class="password-field">[\s\S]*id="reset-password"[\s\S]*class="password-toggle"[\s\S]*<\/div>/',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/<div class="password-field">[\s\S]*id="reset-password-confirm"[\s\S]*class="password-toggle"[\s\S]*<\/div>/',
            $html,
        );
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim Kode OTP');
    }

    public function test_send_otp_creates_hashed_record_and_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.verify-otp', ['email' => $user->email]))
            ->assertSessionHas('status');

        $record = PasswordResetOtp::query()->where('email', $user->email)->firstOrFail();

        $this->assertNull($record->otp);
        $this->assertNotNull($record->token);
        $this->assertNotSame('123456', $record->otp_hash);

        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($record, $user) {
            return $mail->hasTo($user->email)
                && Hash::check($mail->otp, (string) $record->otp_hash);
        });
    }

    public function test_send_otp_with_nonexistent_email_shows_generic_message(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nonexistent@example.com'])
            ->assertRedirect(route('password.verify-otp', ['email' => 'nonexistent@example.com']))
            ->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_verify_otp_page_renders(): void
    {
        $this->get(route('password.verify-otp'))
            ->assertOk()
            ->assertSee('Masukkan Kode OTP');
    }

    public function test_verify_otp_with_valid_code_redirects_to_reset_with_stored_token(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp_digits' => ['1', '2', '3', '4', '5', '6'],
        ])
            ->assertRedirect(route('password.reset', [
                'token' => 'valid-reset-token',
                'email' => $user->email,
            ]));

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
            'verified_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_verify_otp_with_invalid_code_shows_error_and_counts_attempt(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp_digits' => ['9', '9', '9', '9', '9', '9'],
        ])
            ->assertSessionHasErrors(['otp']);

        $this->assertSame(1, PasswordResetOtp::query()->where('email', $user->email)->value('attempts'));
    }

    public function test_verify_otp_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'attempts' => 5,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp_digits' => ['1', '2', '3', '4', '5', '6'],
        ])
            ->assertSessionHasErrors(['otp']);

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
            'verified_at' => null,
        ]);
    }

    public function test_verify_otp_with_expired_code_shows_error(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->post(route('password.verify-otp.submit'), [
            'email' => $user->email,
            'otp_digits' => ['1', '2', '3', '4', '5', '6'],
        ])
            ->assertSessionHasErrors(['otp']);
    }

    public function test_reset_password_with_verified_otp_succeeds(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $this->post(route('password.update'), [
            'token' => 'valid-reset-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('password_reset_otps', [
            'email' => $user->email,
        ]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_reset_password_with_forged_token_fails(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $this->post(route('password.update'), [
            'token' => base64_encode($user->email.'|'.now()->timestamp),
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseHas('password_reset_otps', ['email' => $user->email]);
    }

    public function test_reset_password_requires_at_least_eight_characters(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'token' => 'valid-reset-token',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $this->post(route('password.update'), [
            'token' => 'valid-reset-token',
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertSessionHasErrors(['password']);
    }

    public function test_reset_password_without_verified_otp_fails(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('password.update'), [
            'token' => 'valid-reset-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertSessionHasErrors(['email']);
    }
}
