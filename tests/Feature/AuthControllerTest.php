<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_form_includes_institution_field(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="institution"', false);
    }

    public function test_dosen_registration_stores_institution_in_profile_data(): void
    {
        $this->post(route('register.user'), [
            'name' => 'Dr. Sari Kampus',
            'email' => 'sari.kampus@imersi.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567890',
            'institution' => 'Telkom University',
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'sari.kampus@imersi.id')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('participant', $user->role);
        $this->assertSame('Telkom University', $user->participant?->profile_data['institution'] ?? null);
    }
}
