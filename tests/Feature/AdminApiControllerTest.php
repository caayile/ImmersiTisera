<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbids_dosen_from_admin_overview(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->getJson('/api/admin/overview')
            ->assertForbidden();
    }

    public function test_admin_can_store_department_need(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->postJson('/api/admin/department-needs', [
                'prodi' => 'Informatika',
                'department' => 'Fakultas Teknik',
                'purpose' => 'riset',
                'academic_needs' => 'Studi kasus analitik produk.',
                'problem' => 'Kurang contoh industri.',
                'goal' => 'Modul kuliah baru.',
            ])
            ->assertCreated()
            ->assertJsonPath('prodi', 'Informatika');

        $this->assertDatabaseHas('department_needs', [
            'prodi' => 'Informatika',
            'purpose' => 'riset',
        ]);
    }
}
