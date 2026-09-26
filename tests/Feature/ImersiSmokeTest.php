<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImersiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_outputs_and_final_report_pages_render(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->get(route('participant.outputs'))
            ->assertOk()
            ->assertSee('Output & Evidence', false);

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->get(route('participant.final-report'))
            ->assertOk()
            ->assertSee('Laporan Akhir', false);

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->get(route('mentor.outputs'))
            ->assertOk()
            ->assertSee('Validasi Output', false);
    }

    public function test_outputs_pages_show_history_across_cycles(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $current = $dosen->participant->programs()->latest()->firstOrFail();

        $old = Program::create([
            'participant_id' => $current->participant_id,
            'mentor_id' => $current->mentor_id,
            'department_id' => $current->department_id,
            'business_unit_id' => $current->business_unit_id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-11-01',
            'status' => 'completed',
        ]);
        $old->created_at = now()->subYears(2);
        $old->save();

        ProgramOutput::create([
            'program_id' => $old->id,
            'participant_id' => $current->participant_id,
            'title' => 'Riset Lapangan Terdahulu',
            'type' => 'Insight',
            'description' => 'Hasil magang dua tahun lalu.',
            'status' => 'approved',
        ]);
        ProgramOutput::create([
            'program_id' => $old->id,
            'participant_id' => $current->participant_id,
            'title' => 'Laporan Akhir — Siklus Lama',
            'type' => 'Research Report',
            'description' => 'Laporan penutup siklus lama.',
            'link' => 'https://example.com/hasil-lama',
            'is_final_report' => true,
            'status' => 'approved',
        ]);
        ProgramOutput::create([
            'program_id' => $old->id,
            'participant_id' => $current->participant_id,
            'title' => 'Hasil Utama Siklus Lama',
            'type' => 'Prototype',
            'description' => 'Luaran utama siklus lama.',
            'is_main_output' => true,
            'status' => 'approved',
        ]);
        $old->collaboration()->create([
            'level' => 2,
            'collaboration_type' => 'Guest Lecture',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.outputs'))
            ->assertOk()
            ->assertSee('Riset Lapangan Terdahulu')
            ->assertSee('Industry Insight: Teori vs Praktik Predictive Analytics')
            ->assertSee('Riwayat hasil lintas magang');

        $this->actingAs($dosen)
            ->get(route('participant.final-report'))
            ->assertOk()
            ->assertSee('report-modal-open', false)
            ->assertSee('Laporan Akhir — Siklus Lama')
            ->assertSee('Unit Bisnis')
            ->assertSee('Departement')
            ->assertSee('Judul Laporan')
            ->assertSee('Lihat Hasil')
            ->assertSee('2023')
            ->assertSee('https://example.com/hasil-lama', false)
            ->assertSee('Collaborate');
    }

    public function test_final_report_form_saves_explicit_metadata_and_level(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $tsic = Department::where('name', 'TSIC')->firstOrFail();
        $coe = BusinessUnit::where('name', 'Center Of Excellence')->firstOrFail();

        $this->actingAs($dosen)
            ->post(route('participant.final-report'), [
                'title' => 'Laporan Akhir — Uji Metadata',
                'type' => 'Research Report',
                'description' => 'Ringkasan uji.',
                'is_final_report' => '1',
                'department_id' => $tsic->id,
                'business_unit_id' => $coe->id,
                'year' => '2026',
                'link' => 'https://example.com/laporan-uji',
                'level' => '3',
                'laporan_link' => 'https://example.com/dokumen-uji',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('outputs', [
            'program_id' => $program->id,
            'title' => 'Laporan Akhir — Uji Metadata',
            'is_final_report' => true,
            'department_id' => $tsic->id,
            'business_unit_id' => $coe->id,
            'year' => '2026',
            'link' => 'https://example.com/laporan-uji',
            'laporan_link' => 'https://example.com/dokumen-uji',
        ]);
        $this->assertDatabaseHas('collaboration_pipelines', [
            'program_id' => $program->id,
            'level' => 3,
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.final-report'))
            ->assertOk()
            ->assertSee('Laporan Akhir — Uji Metadata')
            ->assertSee('Menunggu mentor')
            ->assertSee('Lihat Hasil')
            ->assertSee('Lihat Laporan')
            ->assertSee('Develop');
    }

    public function test_final_report_rejects_link_and_file_together(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.final-report'))
            ->post(route('participant.final-report'), [
                'title' => 'Laporan Akhir — Ganda',
                'type' => 'Research Report',
                'is_final_report' => '1',
                'link' => 'https://example.com/hasil',
                'hasil_file' => UploadedFile::fake()->create('hasil.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('link');

        $this->actingAs($dosen)
            ->from(route('participant.final-report'))
            ->post(route('participant.final-report'), [
                'title' => 'Laporan Akhir — Ganda',
                'type' => 'Research Report',
                'is_final_report' => '1',
                'file' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
                'laporan_link' => 'https://example.com/dokumen',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('file');
    }

    public function test_final_report_file_replaces_link_and_removes_old_file(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        $this->actingAs($dosen)
            ->post(route('participant.final-report'), [
                'title' => 'Laporan Akhir — Berkas',
                'type' => 'Research Report',
                'is_final_report' => '1',
                'file' => UploadedFile::fake()->create('awal.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $firstPath = ProgramOutput::where('program_id', $program->id)->where('is_final_report', true)->firstOrFail()->file_path;
        $this->assertNotNull($firstPath);
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($dosen)
            ->post(route('participant.final-report'), [
                'title' => 'Laporan Akhir — Berkas',
                'type' => 'Research Report',
                'is_final_report' => '1',
                'file' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertSame(1, ProgramOutput::where('program_id', $program->id)->where('is_final_report', true)->count());
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_final_report_resubmit_updates_same_record(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        $payload = [
            'title' => 'Laporan Akhir — Revisi',
            'type' => 'Research Report',
            'description' => 'Ringkasan revisi.',
            'is_final_report' => '1',
        ];

        $this->actingAs($dosen)->post(route('participant.final-report'), $payload)->assertRedirect();
        $this->actingAs($dosen)->post(route('participant.final-report'), $payload)->assertRedirect();

        $this->assertSame(1, ProgramOutput::where('program_id', $program->id)->where('is_final_report', true)->count());
        $this->assertSame('submitted', ProgramOutput::where('program_id', $program->id)->where('is_final_report', true)->firstOrFail()->status);
    }

    public function test_public_and_role_homes_render(): void
    {
        $this->seed();

        $this->get('/')->assertOk()->assertSee('Imersi');
        $this->get('/departments')->assertOk()->assertSee('TSPM')->assertSee('K33')->assertSee('WJL')->assertSee('Unit Bisnis Mitra');
        $this->get('/departments/tspm')->assertOk()->assertSee('Digital Business');
        $this->get('/berita')
            ->assertOk()
            ->assertSee('Semua berita')
            ->assertSee('data-reveal', false)
            ->assertSee('tap-feedback', false);
        $this->get('/')
            ->assertOk()
            ->assertSee('10 Kemampuan Inti Sistem')
            ->assertSee('Unit bisnis pilihan gelombang')
            ->assertSee('Berita Terbaru');

        $this->actingAs(User::where('email', 'dosen@imersi.id')->first())
            ->get('/profil')
            ->assertOk()
            ->assertSee('Dr. Andi Pratama')
            ->assertSee('Dosen')
            ->assertSee('Informatika');
        $this->get('/login')->assertOk()->assertSee('Masuk Dosen')->assertSee('Masuk Mentor');
        $this->get('/register')->assertOk()->assertSee('Daftar Dosen')->assertSee('Daftar Mentor');

        $this->post('/login/mentor', ['email' => 'dosen@imersi.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->post('/login/user', ['email' => 'admin@imersi.id', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $this->post('/login/user', ['email' => 'dosen@imersi.id', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->actingAs(User::where('email', 'dosen@imersi.id')->first())
            ->get('/participant/dashboard')
            ->assertOk()
            ->assertSee('Dr. Andi Pratama')
            ->assertSee('Informatika')
            ->assertSee('Fakultas Teknik')
            ->assertSee('Pilih mitra magang dosen Anda')
            ->assertDontSee('Universitas');

        $this->actingAs(User::where('email', 'dosen@imersi.id')->first())
            ->get('/')
            ->assertOk()
            ->assertSee('Beranda')
            ->assertSee('Unit Bisnis')
            ->assertSee('Unit bisnis pilihan gelombang')
            ->assertSee('Dr. Andi')
            ->assertSee('dosen@imersi.id')
            ->assertDontSee('Riwayat Pendaftaran')
            ->assertSee('Dasbor program')
            ->assertDontSee('Pilih mitra magang dosen Anda');

        $this->actingAs(User::where('email', 'mentor@imersi.id')->first())
            ->get('/mentor/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan Mentor');

        $this->actingAs(User::where('email', 'admin@imersi.id')->first())
            ->get('/')
            ->assertOk()
            ->assertSee('Beranda');

        $this->actingAs(User::where('email', 'admin@imersi.id')->first())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Pengelola Program');

        $this->actingAs(User::where('email', 'dosen@imersi.id')->first())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_guests_must_login_to_see_profile(): void
    {
        $this->get('/')->assertOk()->assertDontSee('>Profil</a>', false);
        $this->get('/profil')->assertRedirect(route('login'));
    }

    public function test_admin_can_edit_business_units_and_departments(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $businessUnit = Department::where('slug', 'tspm')->firstOrFail();
        $department = BusinessUnit::where('department_id', $businessUnit->id)->where('name', 'IT')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/departments', [
                'name' => 'Unit Bisnis Baru',
                'description' => 'Deskripsi awal',
                'function' => 'Fungsi awal',
                'area' => 'Area awal',
            ])
            ->assertRedirect();

        $newBusinessUnit = Department::where('slug', 'unit-bisnis-baru')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.departments.update', $newBusinessUnit), [
                'name' => 'Unit Bisnis Diperbarui',
                'description' => 'Deskripsi diperbarui',
                'function' => 'Fungsi diperbarui',
                'area' => 'Area diperbarui',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('departments', [
            'id' => $newBusinessUnit->id,
            'name' => 'Unit Bisnis Diperbarui',
            'slug' => 'unit-bisnis-diperbarui',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.departments.update', $businessUnit), [
                'name' => 'TSPM Updated',
                'description' => 'Updated unit business',
                'function' => 'Updated function',
                'area' => 'Updated area',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.units.update', $department), [
                'department_id' => $businessUnit->id,
                'name' => 'IT Updated',
                'description' => 'Updated department',
                'function' => 'Updated function',
                'work_done' => 'Updated work',
                'example_activities' => 'Updated activities',
                'requirements' => 'Updated requirements',
                'relevant_programs' => 'Informatika, Sistem Informasi',
                'period' => '8 minggu',
                'status' => 'closed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('departments', ['id' => $businessUnit->id, 'name' => 'TSPM Updated', 'area' => 'Updated area']);
        $this->assertDatabaseHas('business_units', [
            'id' => $department->id,
            'name' => 'IT Updated',
            'department_id' => $businessUnit->id,
            'work_done' => 'Updated work',
            'period' => '8 minggu',
            'status' => 'closed',
        ]);
        $this->assertSame(['Informatika', 'Sistem Informasi'], $department->fresh()->relevant_programs);
    }

    public function test_admin_can_manage_news(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'admin@imersi.id')->first())
            ->post('/admin/news', [
                'title' => 'Berita uji admin',
                'excerpt' => 'Ringkasan singkat berita uji.',
                'body' => 'Isi lengkap berita uji untuk memastikan admin bisa menambah berita.',
                'category' => 'Pengumuman',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->get('/berita')->assertOk()->assertSee('Berita uji admin');
        $this->get('/berita/berita-uji-admin')->assertOk()->assertSee('Isi lengkap berita uji');
    }

    public function test_api_login_returns_token(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'email' => 'dosen@imersi.id',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.email', 'dosen@imersi.id')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }
}
