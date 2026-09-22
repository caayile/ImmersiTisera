<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantSidebarProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_dashboard_sidebar_shows_photo_name_nidn_prodi_and_faculty_without_university(): void
    {
        $dosen = User::factory()->create([
            'name' => 'Okky Puspa Ningrum',
            'role' => 'participant',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        Participant::create([
            'user_id' => $dosen->id,
            'nidn' => '0012345678',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Sistem Informasi',
            'expertise' => ['Sistem Informasi'],
            'competency' => ['Analisis'],
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertSee('Okky Puspa Ningrum')
            ->assertSee('0012345678')
            ->assertSee('Sistem Informasi')
            ->assertSee('Fakultas Teknik')
            ->assertSee('https://example.com/avatar.jpg', false)
            ->assertSee('Prodi')
            ->assertSee('Fakultas')
            ->assertDontSee('Universitas')
            ->assertDontSee('University');
    }

    public function test_api_me_includes_avatar_for_sidebar_profile(): void
    {
        $dosen = User::factory()->create([
            'role' => 'participant',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        Participant::create([
            'user_id' => $dosen->id,
            'nidn' => '0099887766',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
            'expertise' => [],
            'competency' => [],
        ]);

        $this->actingAs($dosen)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('avatar', 'https://example.com/avatar.jpg')
            ->assertJsonPath('dosen_profile.nidn', '0099887766')
            ->assertJsonPath('dosen_profile.prodi', 'Informatika')
            ->assertJsonPath('dosen_profile.department', 'Fakultas Teknik');
    }
}
