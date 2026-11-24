<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackendPerformanceTest extends TestCase
{
    public function test_opportunity_list_uses_a_bounded_number_of_queries(): void
    {
        $this->seed();
        $user = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($user);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/api/opportunities');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk()->assertJsonFragment([
            'title' => 'Digital Business',
        ]);

        $this->assertLessThanOrEqual(12, count($queries));
    }

    public function test_search_api_serves_repeated_terms_from_cache(): void
    {
        $this->seed();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $first = $this->getJson('/api/search?q=Digital');

        $firstQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();

        $second = $this->getJson('/api/search?q=Digital');

        $secondQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $first->assertOk();
        $second->assertOk();

        $this->assertSame($first->json(), $second->json());
        $this->assertLessThanOrEqual(2, $firstQueryCount);
        $this->assertSame(0, $secondQueryCount);
    }

    public function test_participant_dashboard_does_not_write_progress_on_get(): void
    {
        $this->seed();
        $user = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $user->participant->id)->firstOrFail();

        $program->update(['progress' => 5, 'current_week' => 1]);

        $this->actingAs($user);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('participant.dashboard'))->assertOk();

        $queries = collect(DB::getQueryLog())->pluck('query')->all();
        DB::disableQueryLog();

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'progress' => 5,
            'current_week' => 1,
        ]);

        $programUpdates = array_filter(
            $queries,
            fn (string $query): bool => str_contains(strtolower($query), 'update "programs"'),
        );

        $this->assertSame([], array_values($programUpdates));
    }
}
