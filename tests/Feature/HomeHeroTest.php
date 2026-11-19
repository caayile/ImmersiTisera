<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicController;
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
            ->assertSee('images/hero/beranda-rotasi-1.jpg', false)
            ->assertSee('images/hero/beranda-rotasi-2.png', false)
            ->assertSee('home-hero__photo--swap', false);
    }

    public function test_home_hero_rotates_between_images_every_three_and_a_half_seconds(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('animation: home-hero-crossfade 7s ease-in-out infinite;', $css);
    }

    public function test_home_hero_wash_keeps_the_background_photo_visible(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('rgb(255 255 255 / 78%) 0%', $css);
        $this->assertStringContainsString('rgb(255 255 255 / 10%) 100%', $css);
    }

    public function test_cached_homepage_stays_portable_across_hosts_and_ports(): void
    {
        $firstResponse = $this->get('http://satu.test:8123/')->assertOk();

        $cacheControl = $firstResponse->headers->get('Cache-Control');
        $this->assertIsString($cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);

        $firstHtml = $firstResponse->getContent();

        $this->assertIsString($firstHtml);
        $this->assertStringNotContainsString('satu.test', $firstHtml);

        $cachedHtml = Cache::get(PublicController::HOME_CACHE_KEY);
        $this->assertIsString($cachedHtml);
        $this->assertStringNotContainsString('satu.test', $cachedHtml);
        $this->assertStringContainsString('href="/', $cachedHtml);

        $secondHtml = $this->get('http://dua.test/')
            ->assertOk()
            ->getContent();

        $this->assertIsString($secondHtml);
        $this->assertStringNotContainsString('dua.test', $secondHtml);
        $this->assertStringContainsString('href="/', $secondHtml);
        $this->assertStringContainsString('images/hero/beranda-rotasi-1.jpg', $secondHtml);
    }

    public function test_admin_can_replace_the_home_background_and_clears_the_public_cache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(PublicController::HOME_CACHE_KEY, '<html>stale</html>', now()->addMinutes(10));

        $this->actingAs($admin)
            ->post(route('admin.settings.home-hero'), [
                'background' => UploadedFile::fake()->image('gedung.jpg', 1200, 800),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Gambar latar beranda diperbarui.');

        $this->assertFalse(Cache::has(PublicController::HOME_CACHE_KEY));

        $setting = HeroSetting::query()->where('page', 'home')->firstOrFail();
        $this->assertTrue(str_starts_with($setting->background_path, 'database-media/'));

        $mediaId = substr($setting->background_path, strlen('database-media/'));

        $this->get('/')
            ->assertSee(route('media.show', $mediaId), false)
            ->assertSee('images/hero/beranda-rotasi-2.png', false)
            ->assertDontSee('images/hero/beranda-rotasi-1.jpg', false);
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
