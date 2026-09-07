<?php

namespace Tests\Feature;

use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepartmentHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_page_shows_campus_hero_and_slides(): void
    {
        $this->seed();

        $this->get('/departments')
            ->assertOk()
            ->assertSee('Departemen Mitra Imersi')
            ->assertSee('K33')
            ->assertSee('Assalaam Hypermarket')
            ->assertSee('SD Al-Firdaus')
            ->assertSee('Filter')
            ->assertSee('Bidang')
            ->assertSee('IT')
            ->assertSee('images/hero/campus.jpg', false);

        $this->get('/departments?field[]=IT')
            ->assertOk()
            ->assertSee('IT')
            ->assertDontSee('>Buyer</h3>', false);
    }

    public function test_admin_can_update_background_and_slides(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/department-hero')
            ->assertOk()
            ->assertSee('Hero Departemen')
            ->assertSee('Background kampus');

        $this->actingAs($admin)
            ->post('/admin/department-hero/background', [
                'title' => 'Mitra Kampus TSU',
                'subtitle' => 'Background baru',
                'background' => UploadedFile::fake()->image('kampus-baru.jpg', 1200, 700),
            ])
            ->assertRedirect();

        $setting = HeroSetting::forPage('departments');
        $this->assertSame('Mitra Kampus TSU', $setting->title);
        $this->assertNotNull($setting->background_path);
        Storage::disk('public')->assertExists($setting->background_path);

        $this->actingAs($admin)
            ->post('/admin/department-hero/slides', [
                'title' => 'WJL',
                'subtitle' => 'Produksi dan marketing',
                'link_url' => '/departments/wjl',
                'sort_order' => 4,
                'image' => UploadedFile::fake()->image('wjl.jpg', 900, 500),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hero_slides', [
            'page' => 'departments',
            'title' => 'WJL',
            'link_url' => '/departments/wjl',
        ]);

        $slide = HeroSlide::where('title', 'WJL')->firstOrFail();
        Storage::disk('public')->assertExists($slide->image_path);

        $this->get('/departments')->assertOk()->assertSee('WJL');
    }
}
