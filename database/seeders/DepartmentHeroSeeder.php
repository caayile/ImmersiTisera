<?php

namespace Database\Seeders;

use App\Models\HeroSetting;
use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class DepartmentHeroSeeder extends Seeder
{
    public function run(): void
    {
        HeroSetting::updateOrCreate(
            ['page' => 'departments'],
            [
                'title' => 'Departemen Mitra Imersi',
                'subtitle' => 'Jelajahi lokasi industri dan pendidikan mitra TSU.',
                'background_path' => 'images/hero/campus.jpg',
            ],
        );

        $slides = [
            [
                'title' => 'K33',
                'subtitle' => 'Operasi gudang, seragam, dan rantai pasok industri.',
                'image_path' => 'images/hero/k33.jpg',
                'link_url' => '/departments/k33',
                'sort_order' => 1,
            ],
            [
                'title' => 'Assalaam Hypermarket',
                'subtitle' => 'Retail modern: store operation hingga marketing.',
                'image_path' => 'images/hero/assalaam.jpg',
                'link_url' => '/departments?q=Assalaam',
                'sort_order' => 2,
            ],
            [
                'title' => 'SD Al-Firdaus',
                'subtitle' => 'Mitra pendidikan dasar untuk prodi PGSD.',
                'image_path' => 'images/hero/al-firdaus.jpg',
                'link_url' => '/departments/sd-al-firdaus',
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::updateOrCreate(
                [
                    'page' => 'departments',
                    'title' => $slide['title'],
                ],
                [
                    ...$slide,
                    'page' => 'departments',
                    'is_active' => true,
                ],
            );
        }
    }
}
