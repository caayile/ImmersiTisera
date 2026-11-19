<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\RegistrationOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiRegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_register_only_creates_a_pending_participant_with_hashed_otp(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Api Dosen',
            'email' => 'api.dosen@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'mentor',
        ])->assertAccepted();

        $this->assertDatabaseMissing('users', ['email' => 'api.dosen@example.com']);

        $pending = RegistrationOtp::query()->where('email', 'api.dosen@example.com')->firstOrFail();

        $this->assertSame('participant', $pending->role);
        $this->assertNull($pending->otp);
        $this->assertNotNull($pending->otp_hash);
    }

    public function test_api_register_requires_eight_character_password(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Api Dosen',
            'email' => 'api.dosen@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->assertDatabaseMissing('registration_otps', ['email' => 'api.dosen@example.com']);
    }

    public function test_api_verify_otp_creates_participant_and_issues_token(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Api Dosen',
            'email' => 'api.dosen@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertAccepted();

        $this->postJson('/api/register/verify-otp', [
            'email' => 'api.dosen@example.com',
            'otp' => $this->sentOtp('api.dosen@example.com'),
        ])->assertCreated()->assertJsonStructure(['token', 'user']);

        $user = User::query()->where('email', 'api.dosen@example.com')->firstOrFail();

        $this->assertSame('participant', $user->role);
        $this->assertTrue($user->mentor === null);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseMissing('registration_otps', ['email' => 'api.dosen@example.com']);
    }

    public function test_api_verify_otp_rejects_wrong_code_and_counts_attempt(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Api Dosen',
            'email' => 'api.dosen@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->postJson('/api/register/verify-otp', [
            'email' => 'api.dosen@example.com',
            'otp' => '000000',
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');

        $this->assertSame(1, RegistrationOtp::query()->where('email', 'api.dosen@example.com')->value('attempts'));
        $this->assertDatabaseMissing('users', ['email' => 'api.dosen@example.com']);
    }

    public function test_api_verify_otp_is_locked_after_five_failed_attempts(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Api Dosen',
            'email' => 'api.dosen@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        RegistrationOtp::query()->where('email', 'api.dosen@example.com')->update(['attempts' => 5]);

        $this->postJson('/api/register/verify-otp', [
            'email' => 'api.dosen@example.com',
            'otp' => $this->sentOtp('api.dosen@example.com'),
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');

        $this->assertDatabaseMissing('users', ['email' => 'api.dosen@example.com']);
    }

    private function sentOtp(string $email): string
    {
        $mail = collect(Mail::sent(RegistrationOtpMail::class))
            ->first(fn (RegistrationOtpMail $sent): bool => $sent->hasTo($email));

        $this->assertNotNull($mail);

        return $mail->otp;
    }
}
