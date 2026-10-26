<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\RegistrationOtp;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_password_toggle_is_pinned_to_the_right(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="login-password"', false)
            ->assertSee('class="password-toggle"', false)
            ->assertSee('right: 0;', false);
    }

    public function test_register_form_leaves_phone_and_institution_for_the_profile(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('Nomor WhatsApp', false)
            ->assertDontSee('Perguruan Tinggi', false)
            ->assertDontSee('name="phone"', false)
            ->assertDontSee('name="institution"', false)
            ->assertSee('name="nik"', false);
    }

    public function test_register_form_includes_mentor_nik_field(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="nik"', false);
    }

    public function test_mentor_registration_stores_nik_after_otp(): void
    {
        Mail::fake();

        $this->post(route('register.mentor'), [
            'name' => 'Budi Industri',
            'email' => 'budi.industri@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567891',
            'nik' => 'TS-2026-001',
        ])->assertRedirect(route('register.verify-otp', ['email' => 'budi.industri@imersi.id']));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'budi.industri@imersi.id']);

        $this->completeRegistrationOtp('budi.industri@imersi.id')
            ->assertRedirect(route('mentor.dashboard'));

        $user = User::where('email', 'budi.industri@imersi.id')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('mentor', $user->role);
        $this->assertNull($user->phone);
        $this->assertSame('TS-2026-001', $user->mentor?->nik);
    }

    public function test_dosen_registration_leaves_phone_and_institution_empty(): void
    {
        Mail::fake();

        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567890',
            'institution' => 'Telkom University',
        ])->assertRedirect(route('register.verify-otp', ['email' => 'sari.kampus@imersi.id']));

        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail): bool {
            return $mail->hasTo('sari.kampus@imersi.id');
        });

        $this->completeRegistrationOtp('sari.kampus@imersi.id')
            ->assertRedirect(route('home'));

        $user = User::where('email', 'sari.kampus@imersi.id')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('participant', $user->role);
        $this->assertNull($user->phone);
        $this->assertNull($user->participant?->profile_data);
    }

    public function test_wrong_registration_otp_does_not_create_account(): void
    {
        Mail::fake();

        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        RegistrationOtp::query()->where('email', 'sari.kampus@imersi.id')->update(['otp' => '123456']);

        $this->post(route('register.verify-otp.submit'), [
            'email' => 'sari.kampus@imersi.id',
            'otp_digits' => ['0', '0', '0', '0', '0', '0'],
        ])->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'sari.kampus@imersi.id']);
    }

    public function test_expired_registration_otp_is_rejected(): void
    {
        Mail::fake();

        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->travel(11)->minutes();

        $otp = RegistrationOtp::query()->where('email', 'sari.kampus@imersi.id')->firstOrFail()->otp;

        $this->post(route('register.verify-otp.submit'), [
            'email' => 'sari.kampus@imersi.id',
            'otp_digits' => str_split($otp),
        ])->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'sari.kampus@imersi.id']);
    }

    public function test_login_before_registration_otp_returns_to_verification(): void
    {
        Mail::fake();

        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->post(route('login.user'), [
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
        ])->assertRedirect(route('register.verify-otp', ['email' => 'sari.kampus@imersi.id']))
            ->assertSessionHasErrors('otp');

        $this->assertGuest();
    }

    public function test_dosen_registration_appears_in_admin_only_after_otp(): void
    {
        Mail::fake();
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567890',
            'institution' => 'Telkom University',
        ]);

        Notification::assertNothingSent();

        $this->completeRegistrationOtp('sari.kampus@imersi.id');

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Dr. Sari Kampus')
            ->assertSee('sari.kampus@imersi.id');

        $this->actingAs($admin)
            ->get(route('admin.participants'))
            ->assertOk()
            ->assertSee('Dr. Sari Kampus');

        Notification::assertSentTo($admin, ImersiAlert::class, function (ImersiAlert $notification) {
            return $notification->title === 'Dosen baru mendaftar'
                && $notification->message === 'Dr. Sari Kampus (sari.kampus@imersi.id) baru membuat akun.'
                && $notification->url === route('admin.users');
        });
    }

    public function test_mentor_registration_appears_in_admin_only_after_otp(): void
    {
        Mail::fake();
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->post(route('register.mentor'), [
            'name' => 'Budi Industri',
            'email' => 'budi.industri@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nik' => 'TS-2026-001',
        ]);

        Notification::assertNothingSent();

        $this->completeRegistrationOtp('budi.industri@imersi.id');

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Budi Industri')
            ->assertSee('budi.industri@imersi.id');

        $this->actingAs($admin)
            ->get(route('admin.mentors'))
            ->assertOk()
            ->assertSee('Budi Industri');

        Notification::assertSentTo($admin, ImersiAlert::class, function (ImersiAlert $notification) {
            return $notification->title === 'Mentor baru mendaftar'
                && $notification->message === 'Budi Industri (budi.industri@imersi.id) baru membuat akun.'
                && $notification->url === route('admin.users');
        });
    }

    private function completeRegistrationOtp(string $email): TestResponse
    {
        $otp = RegistrationOtp::query()->where('email', $email)->firstOrFail()->otp;

        return $this->post(route('register.verify-otp.submit'), [
            'email' => $email,
            'otp_digits' => str_split($otp),
        ]);
    }

    public function test_login_mentor_path_opens_mentor_login_form(): void
    {
        $this->followingRedirects()
            ->get(route('login.mentor'))
            ->assertOk()
            ->assertSee('Masuk Portal Mentor')
            ->assertSee('action="'.route('login.mentor').'"', false);
    }

    public function test_login_user_path_opens_dosen_login_form(): void
    {
        $this->followingRedirects()
            ->get(route('login.user'))
            ->assertOk()
            ->assertSee('Masuk Portal Dosen')
            ->assertSee('action="'.route('login.user').'"', false);
    }
}
