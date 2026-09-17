<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_is_provided(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_login_returns_token_and_mapped_dosen_profile(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'email' => 'dosen@imersi.id',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'user')
            ->assertJsonPath('user.dosen_profile.prodi', 'Informatika')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'dosen_profile']]);
    }

    public function test_returns_422_when_login_credentials_are_invalid(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'email' => 'dosen@imersi.id',
            'password' => 'salah-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_session_user_can_read_me_payload(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('role', 'user')
            ->assertJsonPath('dosen_profile.prodi', 'Informatika');
    }

    public function test_guest_is_redirected_to_login_for_spa(): void
    {
        $this->get('/app')->assertRedirect(route('login'));
        $this->get('/app/opportunities')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_receives_spa_shell(): void
    {
        $this->seed();
        $this->withoutVite();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->get(route('spa'))
            ->assertOk()
            ->assertSee('id="root"', false);
    }
}
