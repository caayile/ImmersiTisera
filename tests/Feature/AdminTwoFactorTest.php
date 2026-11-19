<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_totp_codes_verify_within_the_time_window(): void
    {
        $secret = TwoFactorService::generateSecret();
        $code = TwoFactorService::currentCode($secret);

        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $secret);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertTrue(TwoFactorService::verifyCode($secret, $code));
        $this->assertFalse(TwoFactorService::verifyCode($secret, $code === '000000' ? '111111' : '000000'));
    }

    public function test_admin_login_requires_two_factor_setup(): void
    {
        $this->seed();

        $this->post(route('login.user'), [
            'email' => 'admin@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.setup'));

        $this->assertAuthenticated();

        $this->get(route('admin.dashboard'))->assertRedirect(route('two-factor.setup'));
        $this->get(route('two-factor.setup'))->assertOk()->assertSee('Amankan Akun Administrator');
    }

    public function test_admin_can_enable_two_factor_with_a_valid_code(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $setupResponse = $this->actingAs($admin)->get(route('two-factor.setup'))->assertOk();
        $secret = $admin->fresh()->two_factor_secret;

        $this->assertNotNull($secret);
        $setupResponse->assertSee($secret, false);

        $this->actingAs($admin)
            ->post(route('two-factor.enable'), [
                'code' => TwoFactorService::currentCode($secret),
            ])
            ->assertRedirect(route('admin.dashboard'));

        $admin->refresh();

        $this->assertNotNull($admin->two_factor_confirmed_at);
        $this->assertCount(8, $admin->two_factor_recovery_codes);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_two_factor_enable_rejects_an_invalid_code(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($admin)->get(route('two-factor.setup'))->assertOk();
        $secret = (string) $admin->fresh()->two_factor_secret;
        $code = TwoFactorService::currentCode($secret);

        $this->actingAs($admin)
            ->post(route('two-factor.enable'), [
                'code' => $code === '000000' ? '111111' : '000000',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull($admin->fresh()->two_factor_confirmed_at);
    }

    public function test_admin_with_two_factor_must_pass_the_challenge_to_log_in(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $secret = TwoFactorService::generateSecret();

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => TwoFactorService::hashRecoveryCodes(['ABCD123456']),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.user'), [
            'email' => 'admin@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('two-factor.challenge'))->assertOk()->assertSee('Verifikasi Dua Langkah');

        $this->post(route('two-factor.challenge.submit'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->post(route('two-factor.challenge.submit'), [
            'code' => TwoFactorService::currentCode($secret),
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin->fresh());
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_recovery_code_is_accepted_only_once(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $secret = TwoFactorService::generateSecret();

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => TwoFactorService::hashRecoveryCodes(['ABCD123456', 'EFGH987654']),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.user'), [
            'email' => 'admin@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->post(route('two-factor.challenge.submit'), ['code' => 'abcd123456'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin->fresh());
        $this->assertCount(1, $admin->fresh()->two_factor_recovery_codes);

        $this->post(route('logout'));
        $this->assertGuest();

        $this->post(route('login.user'), [
            'email' => 'admin@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->post(route('two-factor.challenge.submit'), ['code' => 'ABCD123456'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_admin_can_disable_two_factor_with_password_and_code(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $secret = TwoFactorService::generateSecret();

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => TwoFactorService::hashRecoveryCodes(['ABCD123456']),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('two-factor.disable'), [
                'password' => 'password',
                'code' => TwoFactorService::currentCode($secret),
            ])
            ->assertRedirect(route('admin.dashboard'));

        $admin->refresh();

        $this->assertNull($admin->two_factor_secret);
        $this->assertNull($admin->two_factor_recovery_codes);
        $this->assertNull($admin->two_factor_confirmed_at);
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_google_login_cannot_bypass_admin_two_factor(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $admin = User::factory()->create([
            'email' => 'dosen.google@imersi.id',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->get(route('login.google', ['role' => 'dosen']))
            ->assertRedirect(route('two-factor.setup'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertRedirect(route('two-factor.setup'));
    }

    public function test_non_admin_login_is_not_affected_by_two_factor(): void
    {
        $this->seed();

        $this->post(route('login.user'), [
            'email' => 'dosen@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }
}
