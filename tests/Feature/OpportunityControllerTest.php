<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/opportunities')->assertUnauthorized();
    }

    public function test_lists_open_business_units_as_opportunities(): void
    {
        $this->seed();

        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->getJson('/api/opportunities')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $unit->id,
                'title' => 'Digital Business',
            ]);
    }

    public function test_show_includes_match_score_for_dosen(): void
    {
        $this->seed();

        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->getJson('/api/opportunities/'.$unit->id)
            ->assertOk()
            ->assertJsonPath('id', $unit->id)
            ->assertJsonPath('title', 'Digital Business')
            ->assertJsonStructure(['match_score', 'match_label', 'applied']);
    }
}
