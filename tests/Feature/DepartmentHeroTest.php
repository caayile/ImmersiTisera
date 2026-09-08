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
            ->assertSee('Unit Bisnis Mitra Imersi')
            ->assertSee('Tiga Serangkai Pustama Mandiri')
            ->assertSee('Tiga Serangkai Inti Corpora')
            ->assertSee('K33')
            ->assertSee('Wangsa Jastra Lestari')
            ->assertSee('Assalaam Hypermarket')
            ->assertSee('Al - Firdaus')
            ->assertSee('Puspa Holistic Integrative Care')
            ->assertDontSee('Filter')
            ->assertDontSee('Bidang')
            ->assertSee('sticky top-0 z-50 border-b border-line bg-white', false)
            ->assertSee('images/hero/campus.jpg', false);

        $this->get('/departments')
            ->assertOk()
            ->assertSee('Assalam Hypermarket')
            ->assertDontSee('Store Operation')
            ->assertDontSee('Buyer')
            ->assertDontSee('Desain Seragam');
    }

    public function test_departments_page_shows_all_seven_business_units(): void
    {
        $this->seed();

        $html = $this->get('/departments')->assertOk()->getContent();

        foreach (['TSPM', 'TSIC', 'K33', 'WJL', 'Assalam Hypermarket', 'SD Al-Firdaus', 'Puspa Holistic Integrative Care'] as $name) {
            $this->assertStringContainsString($name, $html);
        }

        $this->assertSame(7, substr_count($html, 'Lihat Departemen'));
    }

    public function test_admin_can_update_background_and_slides(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/department-hero')
            ->assertOk()
            ->assertSee('Hero Unit Bisnis')
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

        $this->actingAs($admin)
            ->put('/admin/department-hero/slides/'.$slide->id, [
                'title' => 'Wangsa Jastra Lestari Updated',
                'subtitle' => 'Deskripsi unit bisnis yang diperbarui.',
                'link_url' => '/departments/wjl',
                'sort_order' => 4,
                'is_active' => 1,
                'image' => UploadedFile::fake()->image('wjl-updated.jpg', 900, 500),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'title' => 'Wangsa Jastra Lestari Updated',
            'subtitle' => 'Deskripsi unit bisnis yang diperbarui.',
        ]);
        Storage::disk('public')->assertExists(HeroSlide::findOrFail($slide->id)->image_path);

        $this->get('/departments')->assertOk()->assertSee('WJL');
    }
}
