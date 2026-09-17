<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_index_filters_by_search_query(): void
    {
        $this->seed();

        $this->get(route('departments.index', ['q' => 'TSPM']))
            ->assertOk()
            ->assertSee('TSPM')
            ->assertDontSee('Belum ada unit bisnis.');
    }

    public function test_departments_index_shows_empty_state_when_search_misses(): void
    {
        $this->seed();

        $this->get(route('departments.index', ['q' => 'zzzz-tidak-ada']))
            ->assertOk()
            ->assertSee('Belum ada unit bisnis.');
    }
}
