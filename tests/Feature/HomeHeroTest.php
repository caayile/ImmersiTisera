<?php

namespace Tests\Feature;

use App\Models\HeroSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_the_default_background_image(): void
    {
        $this->get('/')
            ->assertSee('images/hero/beranda.jpg', false)
            ->assertSee('images/hero/beranda-tiga-serangkai.jpg', false)
            ->assertSee('home-hero__photo--swap', false);
    }

    public function test_admin_can_replace_the_home_background_and_clears_the_public_cache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put('public.home.html', '<html>stale</html>', now()->addMinutes(10));

        $this->actingAs($admin)
            ->post(route('admin.settings.home-hero'), [
                'background' => UploadedFile::fake()->image('gedung.jpg', 1200, 800),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Gambar latar beranda diperbarui.');

        $this->assertFalse(Cache::has('public.home.html'));

        $setting = HeroSetting::query()->where('page', 'home')->firstOrFail();
        $this->assertTrue(str_starts_with($setting->background_path, 'database-media/'));

        $mediaId = substr($setting->background_path, strlen('database-media/'));

        $this->get('/')
            ->assertSee(route('media.show', $mediaId), false)
            ->assertSee('images/hero/beranda-tiga-serangkai.jpg', false)
            ->assertDontSee('images/hero/beranda.jpg', false);
    }

    public function test_home_background_upload_requires_a_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.home-hero'), [])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors(['background' => 'gambar latar wajib diisi.']);
    }

    public function test_home_background_upload_rejects_a_file_that_is_not_an_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.home-hero'), [
                'background' => UploadedFile::fake()->create('catatan.txt', 20, 'text/plain'),
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors(['background' => 'gambar latar harus berupa gambar.']);
    }

    public function test_dosen_cannot_replace_the_home_background(): void
    {
        $dosen = User::factory()->create(['role' => 'user']);

        $this->actingAs($dosen)
            ->post(route('admin.settings.home-hero'), [
                'background' => UploadedFile::fake()->image('gedung.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_guest_is_redirected_when_replacing_the_home_background(): void
    {
        $this->post(route('admin.settings.home-hero'), [])
            ->assertRedirect(route('login'));
    }
}
