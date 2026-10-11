<?php

namespace Tests\Feature;

use App\Models\CollaborationPipeline;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaborationAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_collaborations_renders_pipeline_without_type(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $program = Program::firstOrFail();

        CollaborationPipeline::create([
            'program_id' => $program->id,
            'level' => 3,
            'collaboration_type' => null,
            'next_action' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.collaborations'))
            ->assertOk()
            ->assertSee('Pengembangan')
            ->assertSee('—', false);
    }

    public function test_admin_collaborations_lists_typed_pipeline(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $program = Program::firstOrFail();

        CollaborationPipeline::create([
            'program_id' => $program->id,
            'level' => 2,
            'collaboration_type' => 'Research',
            'next_action' => 'Menyusun proposal riset.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.collaborations'))
            ->assertOk()
            ->assertSee('Kolaborasi')
            ->assertSee('Penelitian')
            ->assertSee('Menyusun proposal riset.');
    }
}
