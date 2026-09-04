<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\StudyPrograms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyProgramCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contains_official_faculties_and_programs(): void
    {
        $catalog = StudyPrograms::catalog();

        $this->assertSame([
            'Sistem Informasi',
            'Informatika',
            'Rekayasa Komputer',
        ], $catalog['Fakultas Teknik']);

        $this->assertSame([
            'Manajemen',
            'Psikologi',
            'PGSD',
        ], $catalog['Fakultas Humaniora']);

        $this->assertSame([
            'Teknologi Informasi',
            'Sistem Informasi',
            'Desain Komunikasi Visual',
            'Desain Produksi Tekstil',
        ], $catalog['Sekolah Vokasi']);

        $this->assertTrue(StudyPrograms::isValid('Sekolah Vokasi', 'Desain Produksi Tekstil'));
        $this->assertFalse(StudyPrograms::isValid('Fakultas Teknik', 'PGSD'));

        $targets = StudyPrograms::placementTargets('Desain Produksi Tekstil');
        $this->assertSame('Desain Seragam', $targets[0]['unit']);
        $this->assertContains('K33', $targets[0]['departments']);
        $this->assertSame('Souvenir', $targets[1]['unit']);
    }

    public function test_participant_profile_uses_catalog_dropdowns_not_free_text(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get('/participant/profile')
            ->assertOk()
            ->assertSee('Fakultas Teknik')
            ->assertSee('Sekolah Vokasi')
            ->assertSee('Desain Produksi Tekstil')
            ->assertSee('Fakultas dan program studi')
            ->assertDontSee('name="study_program" value=', false);

        $this->actingAs($dosen)
            ->from('/participant/profile')
            ->post('/participant/profile', [
                'name' => $dosen->name,
                'phone' => '08123456789',
                'nidn' => '00112233',
                'faculty' => 'Fakultas Teknik',
                'study_program' => 'PGSD',
                'competency' => 'AI',
                'expertise' => 'Machine Learning',
            ])
            ->assertSessionHasErrors('study_program');

        $this->actingAs($dosen)
            ->post('/participant/profile', [
                'name' => $dosen->name,
                'phone' => '08123456789',
                'nidn' => '00112233',
                'faculty' => 'Sekolah Vokasi',
                'study_program' => 'Desain Produksi Tekstil',
                'competency' => 'Desain Seragam',
                'expertise' => 'Tekstil',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('participants', [
            'user_id' => $dosen->id,
            'faculty' => 'Sekolah Vokasi',
            'study_program' => 'Desain Produksi Tekstil',
        ]);
    }

    public function test_desain_produksi_tekstil_units_exist_at_k33_and_wjl(): void
    {
        $this->seed();

        $this->assertDatabaseHas('business_units', ['name' => 'Desain Seragam']);
        $this->assertDatabaseHas('business_units', ['name' => 'Souvenir']);

        $this->get('/departments/k33')->assertOk()->assertSee('Desain Seragam')->assertSee('Souvenir');
        $this->get('/departments/wjl')->assertOk()->assertSee('Desain Seragam')->assertSee('Souvenir');
    }
}
