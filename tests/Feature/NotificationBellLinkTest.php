<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationBellLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificates_route_redirects_to_evaluation(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        Participant::create([
            'user_id' => $user->id,
            'nidn' => '001',
            'faculty' => 'Teknik',
            'study_program' => 'Informatika',
        ]);

        $this->actingAs($user)
            ->get('/participant/certificates')
            ->assertRedirect(route('participant.evaluation').'#sertifikat');
    }

    public function test_certificate_notification_link_points_to_evaluation_page(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $dosen->notify(new ImersiAlert(
            'Sertifikat tersedia',
            'Sertifikat magang Anda sudah diterbitkan.',
            'http://localhost/participant/certificates',
        ));

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('/participant/evaluation#sertifikat', false);
    }
}
