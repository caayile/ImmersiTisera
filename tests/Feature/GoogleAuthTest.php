<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_auto_logins_when_unconfigured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get(route('login.google', ['role' => 'dosen']));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'dosen.google@imersi.id',
            'role' => 'participant',
        ]);
        $response->assertRedirect(route('home'));
    }

    public function test_google_redirect_forwards_to_socialite_when_configured(): void
    {
        config([
            'services.google.client_id' => 'dummy-id',
            'services.google.client_secret' => 'dummy-secret',
        ]);

        $mockProvider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $mockProvider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('login.google'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_creates_new_user_and_logs_in(): void
    {
        $mockUser = Mockery::mock(SocialiteUser::class);
        $mockUser->shouldReceive('getId')->andReturn('google-id-12345');
        $mockUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $mockUser->shouldReceive('getName')->andReturn('Google User');
        $mockUser->shouldReceive('getNickname')->andReturn(null);
        $mockUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/photo.jpg');

        $mockProvider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('login.google.callback'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-id-12345',
            'name' => 'Google User',
        ]);
        $response->assertRedirect(route('home'));
    }

    public function test_google_callback_logs_in_existing_user_by_email(): void
    {
        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'role' => 'participant',
        ]);

        $mockUser = Mockery::mock(SocialiteUser::class);
        $mockUser->shouldReceive('getId')->andReturn('google-id-999');
        $mockUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $mockUser->shouldReceive('getName')->andReturn('Existing User');
        $mockUser->shouldReceive('getNickname')->andReturn(null);
        $mockUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/newavatar.jpg');

        $mockProvider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->get(route('login.google.callback'));

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'google_id' => 'google-id-999',
            'avatar' => 'https://lh3.googleusercontent.com/newavatar.jpg',
        ]);
        $response->assertRedirect(route('home'));
    }
}
