<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_index_shows_image_carousel_and_catalog(): void
    {
        $this->seed();

        $this->get(route('departments.index'))
            ->assertOk()
            ->assertSee('Mitra Magang Dosen')
            ->assertSee('Daftar unit bisnis')
            ->assertSee('Cari departemen atau unit bisnis...')
            ->assertSee('dept-hero-carousel')
            ->assertSee('dept-hero-prev')
            ->assertSee('partner-card');
    }

    public function test_departments_page_renders_live_search_and_api_returns_matches(): void
    {
        $this->seed();

        $this->get(route('departments.index'))->assertOk();

        $this->get(route('api.search', ['q' => 'TSPM']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Departemen',
                'name' => 'TSPM',
            ]);
    }

    public function test_public_footer_uses_the_white_footer_logo(): void
    {
        $this->seed();

        $response = $this->get('/')->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'images/logo-footer.png'));
        $response->assertSee('href="https://share.google/SI0q9wACAtHSnEJmR"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('Jl. Prof. DR. Supomo No.23, Sriwedari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57141');
    }

    public function test_homepage_displays_building_background_and_public_navigation(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertSee('Dosen belajar langsung dari industri.')
            ->assertSee('kerja nyata di Tiga Serangkai Group')
            ->assertSee('Beranda')
            ->assertSee('Unit Bisnis')
            ->assertSee('Berita')
            ->assertSee('Masuk')
            ->assertSee('sticky top-0', false);
    }

    public function test_public_pages_share_the_reference_header_layout(): void
    {
        $this->seed();

        $pages = [
            ['/', route('home'), 'Beranda'],
            [route('departments.index'), route('departments.index'), 'Unit Bisnis'],
            [route('news.index'), route('news.index'), 'Berita'],
        ];

        foreach ($pages as [$url, $activeHref, $activeLabel]) {
            $this->get($url)
                ->assertOk()
                ->assertSee('class="home-header sticky top-0 z-50"', false)
                ->assertSee('class="mx-auto flex max-w-[1240px] items-center justify-between gap-4 px-5 py-4 lg:px-0"', false)
                ->assertSee('class="h-10 w-auto object-contain sm:h-11"', false)
                ->assertSee('<a href="'.$activeHref.'" class="home-header__link home-header__link--active">'.$activeLabel.'</a>', false)
                ->assertSee('class="home-header__login"', false);
        }
    }

    public function test_home_hero_shows_admin_batch_name_when_registration_open(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'batch' => 'Batch 1',
            'registration_start' => now()->subDay()->startOfDay(),
            'registration_deadline' => now()->addDays(14)->setTime(23, 59, 59),
            'status' => 'open',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Batch 1')
            ->assertSee('Dibuka')
            ->assertSee('Pendaftaran Batch 1 telah dibuka');
    }

    public function test_home_hero_shows_closed_when_all_deadlines_passed(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'batch' => 'Batch 1',
            'registration_start' => now()->subDays(14)->startOfDay(),
            'registration_deadline' => now()->subDay()->setTime(23, 59, 59),
            'status' => 'open',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pendaftaran<br>Ditutup', false)
            ->assertSee('Pendaftaran saat ini ditutup')
            ->assertDontSee('Batch 1');
    }

    public function test_home_hero_shows_generic_text_when_only_some_units_open(): void
    {
        $this->seed();

        BusinessUnit::query()->update([
            'registration_start' => now()->subDays(14)->startOfDay(),
            'registration_deadline' => now()->subDay()->setTime(23, 59, 59),
            'status' => 'closed',
        ]);

        $unit = BusinessUnit::firstOrFail();
        $unit->update([
            'batch' => 'Batch Parsial',
            'registration_start' => now()->subDay()->startOfDay(),
            'registration_deadline' => now()->addDays(14)->setTime(23, 59, 59),
            'status' => 'open',
        ]);

        $this->assertTrue($unit->fresh()->isOpen());

        $this->get('/')
            ->assertOk()
            ->assertSee('Ada Lowongan<br>Terbuka', false)
            ->assertSee('Ada lowongan yang masih dibuka')
            ->assertDontSee('Batch Parsial')
            ->assertDontSee('Pendaftaran<br>Ditutup', false);
    }
}
