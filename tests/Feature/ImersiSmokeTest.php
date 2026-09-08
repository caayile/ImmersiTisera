<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImersiSmokeTest extends TestCase
{
    use RefreshDatabase;

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

        $this->actingAs(User::where('email', 'dosen@imersi.id')->first())
            ->get('/participant/dashboard')
            ->assertOk()
            ->assertSee('Digital Business')
            ->assertSee('Pilih mitra imersi Anda')
            ->assertSee('K33')
            ->assertSee('WJL');

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
