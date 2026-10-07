<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\BusinessUnit;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LowonganAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'admin@imersi.id')->firstOrFail();
    }

    public function test_admin_can_view_lowongan_index(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.lowongan'))
            ->assertOk()
            ->assertSee($unit->name)
            ->assertSee($unit->department->name);
    }

    public function test_lowongan_index_renders_bulk_open_modal(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->get(route('admin.lowongan'))
            ->assertOk()
            ->assertSee('Buka semua lowongan')
            ->assertSee('Batch pembukaan')
            ->assertSee('bulk-open-modal', false)
            ->assertSee('bulk-toggle', false);
    }

    public function test_admin_can_toggle_lowongan_status(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();
        $unit->update([
            'registration_start' => '2026-09-01 08:00:00',
            'registration_deadline' => '2026-09-30 23:59:00',
            'status' => 'closed',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle', $unit), ['status' => 'open'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => 'open']);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle', $unit), ['status' => 'closed'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => 'closed']);
    }

    public function test_toggle_blocked_when_period_not_set(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();
        $unit->update([
            'registration_start' => null,
            'registration_deadline' => null,
            'status' => 'closed',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle', $unit), ['status' => 'open'])
            ->assertRedirect()
            ->assertSessionHasErrors('lowongan');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => 'closed']);
    }

    public function test_admin_can_open_all_with_batch_and_period(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'batch' => null,
            'registration_start' => null,
            'registration_deadline' => null,
            'status' => 'closed',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => 'Batch 1 - Semester Ganjil',
                'registration_start' => '2026-10-01',
                'registration_deadline' => '2026-10-31',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::where('status', 'closed')->count());
        $this->assertSame(0, BusinessUnit::where('batch', null)->count());
        $this->assertDatabaseHas('business_units', [
            'status' => 'open',
            'batch' => 'Batch 1 - Semester Ganjil',
            'registration_start' => '2026-10-01 00:00:00',
            'registration_deadline' => '2026-10-31 23:59:59',
        ]);
    }

    public function test_open_all_uses_default_batch_when_blank(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => '',
                'registration_start' => now()->toDateTimeString(),
                'registration_deadline' => now()->addDays(30)->toDateTimeString(),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::where('batch', null)->count());
        $this->assertDatabaseHas('business_units', [
            'status' => 'open',
            'batch' => AdminController::batchDefault(),
        ]);
    }

    public function test_open_all_requires_period(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => 'Batch 1',
                'registration_start' => null,
                'registration_deadline' => null,
            ])
            ->assertSessionHasErrors(['registration_start', 'registration_deadline']);
    }

    public function test_open_all_rejects_deadline_before_start(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => 'Batch 1',
                'registration_start' => '2026-10-31',
                'registration_deadline' => '2026-10-01',
            ])
            ->assertSessionHasErrors('registration_deadline');
    }

    public function test_toggle_all_blocked_when_any_unit_missing_period(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();
        $unit->update([
            'registration_start' => null,
            'registration_deadline' => null,
            'status' => 'closed',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle-all'), ['status' => 'open'])
            ->assertRedirect()
            ->assertSessionHasErrors('lowongan');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => 'closed']);
    }

    public function test_toggle_all_closed_works_without_periods(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'registration_start' => null,
            'registration_deadline' => null,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle-all'), ['status' => 'closed'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::where('status', 'open')->count());
    }

    public function test_single_toggle_closed_works_without_period(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();
        $unit->update([
            'registration_start' => null,
            'registration_deadline' => null,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle', $unit), ['status' => 'closed'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => 'closed']);
    }

    public function test_admin_can_update_lowongan_registration_period(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period', $unit), [
                'registration_start' => '2026-10-01',
                'registration_deadline' => '2026-10-31',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('business_units', [
            'id' => $unit->id,
            'registration_start' => '2026-10-01 00:00:00',
            'registration_deadline' => '2026-10-31 23:59:59',
        ]);
    }

    public function test_lowongan_deadline_runs_until_end_of_day(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period', $unit), [
                'registration_start' => '2026-10-01',
                'registration_deadline' => '2026-10-05',
            ])
            ->assertRedirect();

        $unit->refresh();
        $this->assertSame('00:00', $unit->registration_start->format('H:i'));
        $this->assertSame('23:59', $unit->registration_deadline->format('H:i'));

        $this->actingAs($this->admin())
            ->get(route('admin.lowongan'))
            ->assertOk()
            ->assertSee('05 Oct 2026 (23.59)');
    }

    public function test_admin_can_clear_lowongan_period(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period', $unit), [
                'registration_start' => null,
                'registration_deadline' => null,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('business_units', [
            'id' => $unit->id,
            'registration_start' => null,
            'registration_deadline' => null,
        ]);
    }

    public function test_admin_can_toggle_all_lowongan(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'registration_start' => '2026-09-01 08:00:00',
            'registration_deadline' => '2026-09-30 23:59:00',
            'status' => 'closed',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle-all'), ['status' => 'open'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::where('status', 'closed')->count());

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle-all'), ['status' => 'closed'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::where('status', 'open')->count());
    }

    public function test_admin_can_set_period_for_all_lowongan(): void
    {
        $this->seed();

        BusinessUnit::query()->update(['registration_start' => null, 'registration_deadline' => null]);

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period-all'), [
                'registration_start' => '2026-10-01',
                'registration_deadline' => '2026-10-31',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(
            0,
            BusinessUnit::whereNull('registration_start')->orWhereNull('registration_deadline')->count()
        );
        $this->assertDatabaseHas('business_units', ['registration_start' => '2026-10-01 00:00:00', 'registration_deadline' => '2026-10-31 23:59:59']);
    }

    public function test_admin_can_clear_period_for_all_lowongan(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period-all'), [
                'registration_start' => null,
                'registration_deadline' => null,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, BusinessUnit::whereNotNull('registration_start')->count());
        $this->assertSame(0, BusinessUnit::whereNotNull('registration_deadline')->count());
    }

    public function test_toggle_rejects_status_outside_open_and_closed(): void
    {
        $this->seed();

        $unit = BusinessUnit::firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.toggle', $unit), ['status' => 'archived'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('business_units', ['id' => $unit->id, 'status' => $unit->status]);
    }

    public function test_user_sees_open_lowongan_only_within_admin_registration_period(): void
    {
        $this->seed();

        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => 'Batch Uji Periode',
                'registration_start' => now()->subDay()->toDateString(),
                'registration_deadline' => now()->addDays(14)->toDateString(),
            ])
            ->assertRedirect();

        $unit->refresh();
        $this->assertTrue($unit->isOpen());

        $this->get(route('units.show', $unit))
            ->assertOk()
            ->assertSee('Terbuka')
            ->assertSee('Daftar Program');

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.period', $unit), [
                'registration_start' => now()->subDays(30)->toDateString(),
                'registration_deadline' => now()->subDay()->toDateString(),
            ])
            ->assertRedirect();

        $unit->refresh();
        $this->assertSame('open', $unit->status);
        $this->assertFalse($unit->isOpen());

        $this->get(route('units.show', $unit))
            ->assertOk()
            ->assertSee('Pendaftaran ditutup')
            ->assertDontSee('Daftar Program');

        $dosen = User::factory()->create(['role' => 'participant']);
        Participant::create([
            'user_id' => $dosen->id,
            'study_program' => 'Informatika',
            'faculty' => 'Fakultas Teknik',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertRedirect(route('departments.index'));
    }

    public function test_opening_lowongan_clears_public_home_cache(): void
    {
        $this->seed();

        Cache::put('public.home.html', '<html>stale</html>', now()->addMinutes(10));

        $this->actingAs($this->admin())
            ->post(route('admin.lowongan.open-all'), [
                'batch' => 'Batch Cache',
                'registration_start' => now()->toDateString(),
                'registration_deadline' => now()->addDays(14)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertFalse(Cache::has('public.home.html'));
    }
}
