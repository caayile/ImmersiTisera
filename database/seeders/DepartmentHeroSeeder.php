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
                'title' => 'Unit Bisnis Mitra Imersi',
                'subtitle' => 'Jelajahi lokasi industri dan pendidikan mitra TSU.',
                'background_path' => 'images/hero/campus.jpg',
            ],
        );

        $slides = [
            [
                'title' => 'Tiga Serangkai Pustama Mandiri',
                'subtitle' => 'Penerbitan, produksi, penjualan, dan layanan bisnis buku sekolah.',
                'image_path' => 'images/hero/campus.jpg',
                'link_url' => '/departments/tspm',
                'sort_order' => 1,
            ],
            [
                'title' => 'Tiga Serangkai Inti Corpora',
                'subtitle' => 'Pusat keunggulan, pengembangan orang, dan perencanaan inovasi.',
                'image_path' => 'images/hero/campus.jpg',
                'link_url' => '/departments/tsic',
                'sort_order' => 2,
            ],
            [
                'title' => 'K33',
                'subtitle' => 'Operasi penjualan, SDM, keuangan, IT, dan rantai pasok industri.',
                'image_path' => 'images/hero/k33.jpg',
                'link_url' => '/departments/k33',
                'sort_order' => 3,
            ],
            [
                'title' => 'Wangsa Jastra Lestari',
                'subtitle' => 'Produksi, marketing, finance, dan pengelolaan SDM.',
                'image_path' => 'images/hero/k33.jpg',
                'link_url' => '/departments/wjl',
                'sort_order' => 4,
            ],
            [
                'title' => 'Assalam Hypermart',
                'subtitle' => 'Retail modern: store operation, trading, finance, IT, dan marketing.',
                'image_path' => 'images/hero/assalaam.jpg',
                'link_url' => '/departments/assalam-hypermarket',
                'sort_order' => 5,
            ],
            [
                'title' => 'Al - Firdaus',
                'subtitle' => 'Mitra pendidikan dasar untuk prodi PGSD.',
                'image_path' => 'images/hero/al-firdaus.jpg',
                'link_url' => '/departments/sd-al-firdaus',
                'sort_order' => 6,
            ],
            [
                'title' => 'Puspa Holistic Integrative Care',
                'subtitle' => 'Pengembangan holistik, integratif, dan kesejahteraan manusia.',
                'image_path' => 'images/hero/campus.jpg',
                'link_url' => '/departments/puspa-holistic-integrative-care',
                'sort_order' => 7,
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
