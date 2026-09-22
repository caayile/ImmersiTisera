<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
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

    public function test_google_redirect_rejects_when_unconfigured_in_production(): void
    {
        $this->app['env'] = 'production';

        config([
            'app.debug' => false,
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('login.google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_redirect_forwards_to_google_when_configured(): void
    {
        config([
            'services.google.client_id' => 'dummy-id',
            'services.google.client_secret' => 'dummy-secret',
            'services.google.redirect' => '/auth/google/callback',
        ]);

        $response = $this->get(route('login.google', ['role' => 'dosen']));

        $location = $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertNotNull($location);
        $this->assertStringContainsString('accounts.google.com', $location);
        $this->assertStringContainsString('dummy-id', $location);
        $this->assertStringContainsString('auth/google/callback', urldecode($location));
    }

    public function test_google_redirect_moves_loopback_host_to_app_url(): void
    {
        config([
            'app.url' => 'http://localhost',
            'services.google.client_id' => 'dummy-id',
            'services.google.client_secret' => 'dummy-secret',
        ]);

        $this->get('http://127.0.0.1/auth/google?role=dosen')
            ->assertRedirect('http://localhost/auth/google?role=dosen');
    }

    public function test_google_callback_creates_new_user_and_logs_in(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-id-12345',
            'email' => 'googleuser@example.com',
            'name' => 'Google User',
            'nickname' => null,
            'avatar' => 'https://lh3.googleusercontent.com/photo.jpg',
        ]));

        $response = $this->get(route('login.google.callback'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-id-12345',
            'name' => 'Google User',
        ]);
        $this->assertDatabaseHas('participants', [
            'user_id' => User::where('email', 'googleuser@example.com')->value('id'),
        ]);
        $response->assertRedirect(route('home'));
    }

    public function test_google_callback_home_shows_navbar_profile_and_avatar(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-id-navbar',
            'email' => 'google.navbar@example.com',
            'name' => 'Google Navbar',
            'nickname' => null,
            'avatar' => 'https://lh3.googleusercontent.com/navbar-photo.jpg',
        ]));

        $this->followingRedirects()
            ->get(route('login.google.callback'))
            ->assertSee('Google Navbar')
            ->assertDontSee('>Profil</a>', false)
            ->assertSee('https://lh3.googleusercontent.com/navbar-photo.jpg', false)
            ->assertSee(route('profile.public'), false)
            ->assertDontSee('>Masuk</a>', false);
    }

    public function test_google_callback_creates_mentor_when_role_is_mentor(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-mentor-1',
            'email' => 'mentor.oauth@example.com',
            'name' => 'Mentor Google',
        ]));

        $response = $this->withSession(['oauth_role' => 'mentor'])
            ->get(route('login.google.callback'));

        $user = User::where('email', 'mentor.oauth@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('mentor', $user->role);
        $this->assertDatabaseHas('mentors', ['user_id' => $user->id]);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('mentor.dashboard'));
    }

    public function test_google_callback_logs_in_existing_user_by_email(): void
    {
        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'role' => 'participant',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-id-999',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'avatar' => 'https://lh3.googleusercontent.com/newavatar.jpg',
        ]));

        $response = $this->get(route('login.google.callback'));

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'google_id' => 'google-id-999',
            'avatar' => 'https://lh3.googleusercontent.com/newavatar.jpg',
        ]);
        $response->assertRedirect(route('home'));
    }

    public function test_google_callback_rejects_inactive_users(): void
    {
        User::create([
            'name' => 'Disabled User',
            'email' => 'disabled@example.com',
            'password' => 'password',
            'role' => 'participant',
            'status' => 'inactive',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-disabled',
            'email' => 'disabled@example.com',
            'name' => 'Disabled User',
        ]));

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_callback_returns_to_login_when_user_cancels(): void
    {
        $this->get(route('login.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_callback_explains_missing_socialite_package(): void
    {
        Socialite::fake('google', function () {
            throw new \Error('Class "Laravel\Socialite\Facades\Socialite" not found');
        });

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Paket login Google belum terpasang. Di folder proyek jalankan composer install, lalu muat ulang halaman login.',
            ]);

        $this->assertGuest();
    }

    public function test_google_callback_explains_invalid_client_secret(): void
    {
        Socialite::fake('google', function () {
            throw new \RuntimeException('Client error: POST https://www.googleapis.com/oauth2/v4/token resulted in a 401 Unauthorized response: {"error":"invalid_client","error_description":"The provided client secret is invalid."}');
        });

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Client secret Google tidak valid. Salin Client secret yang aktif dari Google Cloud Console (APIs & Services → Credentials → OAuth 2.0 Client) ke GOOGLE_CLIENT_SECRET di .env, simpan, lalu coba lagi.',
            ]);

        $this->assertGuest();
    }

    public function test_google_callback_returns_to_login_when_provider_fails(): void
    {
        Socialite::fake('google', function () {
            throw new \RuntimeException('Unable to fetch user details.');
        });

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Gagal menghubungkan akun Google. Silakan coba lagi.',
            ]);

        $this->assertGuest();
    }

    public function test_login_page_links_to_google_oauth(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('login.google', ['role' => 'dosen']), false)
            ->assertSee('Sambung dengan Google');
    }
}
