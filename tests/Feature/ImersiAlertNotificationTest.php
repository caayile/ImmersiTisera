<?php

namespace Tests\Feature;

use App\Models\Mentor;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ImersiAlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_alert_uses_database_and_mail_channels(): void
    {
        Notification::fake();

        $dosen = User::factory()->create(['role' => 'participant']);

        $dosen->notify(new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.', route('participant.logbooks')));

        Notification::assertSentTo($dosen, ImersiAlert::class, fn (ImersiAlert $notification, array $channels): bool => $channels === ['database', 'mail']
            && $notification->title === 'Program ACTIVE'
            && $notification->message === 'Lanjutkan logbook minggu ini.');
    }

    public function test_mentor_alert_uses_database_and_mail_channels(): void
    {
        Notification::fake();

        $mentor = User::factory()->create(['role' => 'mentor']);

        $mentor->notify(new ImersiAlert('Logbook baru', 'Ada logbook menunggu review.', route('mentor.logbooks')));

        Notification::assertSentTo($mentor, ImersiAlert::class, fn (ImersiAlert $notification, array $channels): bool => $channels === ['database', 'mail']
            && $notification->title === 'Logbook baru');
    }

    public function test_dosen_alert_skips_mail_when_smtp_credentials_are_missing(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.username' => null,
            'mail.mailers.smtp.password' => null,
        ]);

        $dosen = User::factory()->make([
            'email' => 'dosen.notif@example.com',
            'role' => 'participant',
        ]);

        $this->assertSame(['database'], (new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.'))->via($dosen));
    }

    public function test_dosen_alert_uses_mail_when_smtp_credentials_are_present(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.username' => 'magangdosen@gmail.com',
            'mail.mailers.smtp.password' => 'app-password',
        ]);

        $dosen = User::factory()->make([
            'email' => 'dosen.notif@example.com',
            'role' => 'participant',
        ]);

        $this->assertSame(['database', 'mail'], (new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.'))->via($dosen));
    }

    public function test_admin_alert_uses_database_channel_only(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $admin->notify(new ImersiAlert('Matching menunggu review', 'Ada pengajuan baru.', route('admin.matching')));

        Notification::assertSentTo($admin, ImersiAlert::class, fn (ImersiAlert $notification, array $channels): bool => $channels === ['database']);
    }

    public function test_dosen_mail_contains_title_message_and_action(): void
    {
        $dosen = User::factory()->make([
            'name' => 'Dr. Andi Pratama',
            'role' => 'participant',
        ]);

        $mail = (new ImersiAlert(
            'Program ACTIVE',
            'Lanjutkan logbook minggu ini.',
            '/participant/logbooks',
        ))->toMail($dosen);

        $this->assertSame('Program ACTIVE', $mail->subject);
        $this->assertSame('Halo, Dr. Andi Pratama', $mail->greeting);
        $this->assertSame(['Lanjutkan logbook minggu ini.'], $mail->introLines);
        $this->assertSame('Buka di Magang Dosen', $mail->actionText);
        $this->assertSame(url('/participant/logbooks'), $mail->actionUrl);
    }

    public function test_dosen_mail_escapes_dangerous_content(): void
    {
        $dosen = User::factory()->make([
            'name' => "Andi <script>alert('xss')</script>",
            'role' => 'participant',
        ]);

        $html = (new ImersiAlert(
            'Judul <script>alert(1)</script>',
            'Pesan <img src=x onerror=alert(1)>',
            '/participant/logbooks',
        ))->toMail($dosen)->render();

        $this->assertStringNotContainsString("<script>alert('xss')</script>", $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
    }

    public function test_guest_is_redirected_from_dosen_notifications(): void
    {
        $this->get(route('participant.notifications'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_mentor_notifications(): void
    {
        $this->get(route('mentor.notifications'))
            ->assertRedirect(route('login'));
    }

    public function test_mentor_cannot_open_dosen_notifications(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        Mentor::create(['user_id' => $mentor->id]);

        $this->actingAs($mentor)
            ->get(route('participant.notifications'))
            ->assertForbidden();
    }

    public function test_dosen_home_shows_navbar_notifications_and_unread_alert(): void
    {
        $dosen = User::factory()->create([
            'name' => 'Dr. Notif Dosen',
            'role' => 'participant',
        ]);
        $dosen->notify(new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.', route('participant.logbooks')));

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertDontSee('>Notifikasi</a>', false)
            ->assertSee('Notifikasi, 1 belum dibaca')
            ->assertSee('Program ACTIVE')
            ->assertSee('Lanjutkan logbook minggu ini.')
            ->assertSee(route('participant.notifications'), false);
    }

    public function test_dosen_notifications_page_has_a_back_link_to_home(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.notifications'))
            ->assertSee('Kembali')
            ->assertSee(route('home'), false);
    }

    public function test_dosen_notifications_page_lists_alert_and_marks_it_read(): void
    {
        $dosen = User::factory()->create([
            'name' => 'Dr. Notif Dosen',
            'role' => 'participant',
        ]);
        $dosen->notify(new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.', route('participant.logbooks')));

        $this->actingAs($dosen)
            ->get(route('participant.notifications'))
            ->assertSee('Program ACTIVE')
            ->assertSee('Lanjutkan logbook minggu ini.');

        $this->assertSame(0, $dosen->fresh()->unreadNotifications()->count());
    }

    public function test_mentor_dashboard_shows_navbar_notifications_and_unread_alert(): void
    {
        $mentor = User::factory()->create([
            'name' => 'Mentor Notif',
            'role' => 'mentor',
        ]);
        Mentor::create(['user_id' => $mentor->id]);
        $mentor->notify(new ImersiAlert('Logbook baru', 'Ada logbook menunggu review.', route('mentor.logbooks')));

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertSee('Notifikasi')
            ->assertSee('Notifikasi, 1 belum dibaca')
            ->assertSee('Logbook baru')
            ->assertSee(route('mentor.notifications'), false);
    }

    public function test_mentor_notifications_page_has_a_back_link_to_dashboard(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        Mentor::create(['user_id' => $mentor->id]);

        $this->actingAs($mentor)
            ->get(route('mentor.notifications'))
            ->assertSee('Kembali')
            ->assertSee(route('mentor.dashboard'), false);
    }

    public function test_mentor_notifications_page_lists_alert(): void
    {
        $mentor = User::factory()->create([
            'name' => 'Mentor Notif',
            'role' => 'mentor',
        ]);
        Mentor::create(['user_id' => $mentor->id]);
        $mentor->notify(new ImersiAlert('Logbook baru', 'Ada logbook menunggu review.', route('mentor.logbooks')));

        $this->actingAs($mentor)
            ->get(route('mentor.notifications'))
            ->assertSee('Logbook baru')
            ->assertSee('Ada logbook menunggu review.');
    }

    public function test_dosen_notifications_page_escapes_alert_html(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);
        $dosen->notify(new ImersiAlert(
            'Judul <script>alert(1)</script>',
            'Pesan <img src=x onerror=alert(1)>',
            route('participant.logbooks'),
        ));

        $this->actingAs($dosen)
            ->get(route('participant.notifications'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('Judul');
    }
}
