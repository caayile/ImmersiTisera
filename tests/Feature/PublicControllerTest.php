<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_index_shows_image_carousel_and_catalog(): void
    {
        $this->seed();

        $this->get(route('departments.index'))
            ->assertOk()
            ->assertSee('Mitra Magang Dosen')
            ->assertSee('Daftar unit bisnis')
            ->assertSee('Cari departemen atau unit bisnis...')
            ->assertSee('dept-hero-carousel')
            ->assertSee('dept-hero-prev')
            ->assertSee('partner-card');
    }

    public function test_departments_page_renders_live_search_and_api_returns_matches(): void
    {
        $this->seed();

        $this->get(route('departments.index'))->assertOk();

        $this->get(route('api.search', ['q' => 'TSPM']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Departemen',
                'name' => 'TSPM',
            ]);
    }
}
