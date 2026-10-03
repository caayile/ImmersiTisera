<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MentorProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_view_and_update_profile_including_nik(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $unit = BusinessUnit::where('name', 'IT')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.profile'))
            ->assertOk()
            ->assertSee('name="nik"', false)
            ->assertSee('Profil mentor');

        $this->actingAs($mentor)
            ->post(route('mentor.profile.update'), [
                'name' => 'Budi Santoso',
                'phone' => '081200000005',
                'nik' => 'TS-2026-007',
                'position' => 'Head of Digital Business',
                'expertise' => 'Product Analytics, Machine Learning',
                'business_unit_id' => $unit->id,
                'avatar' => UploadedFile::fake()->image('budi.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $mentor->refresh();

        $this->assertSame('TS-2026-007', $mentor->mentor->nik);
        $this->assertSame('Head of Digital Business', $mentor->mentor->position);
        $this->assertSame(['Product Analytics', 'Machine Learning'], $mentor->mentor->expertise);
        $this->assertSame($unit->id, $mentor->mentor->business_unit_id);
        $this->assertTrue(str_starts_with($mentor->avatar, 'database-media/'));
        $this->assertNotNull($mentor->avatarUrl());
    }

    public function test_public_mentor_profile_shows_nik_without_ketersediaan(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $mentor->mentor->update(['nik' => 'TS-2026-007']);

        $this->actingAs($mentor)
            ->get(route('profile.public'))
            ->assertOk()
            ->assertSee('TS-2026-007')
            ->assertDontSee('Ketersediaan');
    }
}
