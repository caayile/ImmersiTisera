<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@imersi.id')->first()
            ?? User::where('role', 'admin')->first();

        $items = [
            [
                'title' => 'Gelombang Imersi 2026 dibuka',
                'excerpt' => 'Pendaftaran dosen untuk program 8 minggu / ±60 hari sudah dibuka di TSPM dan TSIC.',
                'body' => "Pendaftaran dosen untuk program Industry Immersion gelombang 2026 telah dibuka.\n\nDosen dapat memilih departemen dan unit bisnis yang relevan, lalu menempuh perjalanan 8 minggu dengan buku catatan harian, pendampingan mingguan, serta satu hasil utama yang nyata.\n\nSegera lengkapi profil dan ajukan penempatan agar pencocokan dapat diproses.",
                'category' => 'Pengumuman',
                'days' => 1,
            ],
            [
                'title' => 'Digital Business membuka mentoring',
                'excerpt' => 'Unit Digital Business mencari dosen dengan kompetensi analitik, produk digital, dan riset terapan.',
                'body' => "Unit Digital Business membuka kuota mentoring untuk dosen yang tertarik pada produk digital, analitik, dan riset terapan.\n\nPeserta akan mengikuti observasi alur kerja, diskusi mingguan 30 menit, serta menyusun hasil kerja yang bermanfaat bagi unit bisnis dan kampus.",
                'category' => 'Unit Bisnis',
                'days' => 3,
            ],
            [
                'title' => 'Kolaborasi kuliah tamu dimulai',
                'excerpt' => 'Program yang selesai dapat berlanjut ke kuliah tamu, proyek mahasiswa, dan pengembangan kurikulum.',
                'body' => "Setelah tahap imersi selesai, dosen dan mentor dapat melanjutkan ke alur kolaborasi: tindak lanjut, kuliah tamu, riset bersama, hingga pengembangan kurikulum.\n\nHal ini memastikan dampak program tidak berhenti di minggu ke-8.",
                'category' => 'Kolaborasi',
                'days' => 7,
            ],
            [
                'title' => 'Pemeriksaan minggu ke-2: catatan wawasan industri',
                'excerpt' => 'Peserta aktif diminta menyelesaikan catatan wawasan industri sebelum memasuki fase paham.',
                'body' => "Pada minggu 1–2, peserta diharapkan menyelesaikan catatan wawasan industri sebagai target hasil fase Temukan.\n\nDokumen ini menjadi dasar eksplorasi masalah pada minggu 3–4 bersama mentor unit bisnis.",
                'category' => 'Program',
                'days' => 10,
            ],
            [
                'title' => 'Center of Excellence membuka peluang kurikulum industri',
                'excerpt' => 'TSIC membuka ruang kolaborasi dosen untuk merancang pembelajaran berbasis industri.',
                'body' => "Center of Excellence di TSIC membuka peluang bagi dosen yang ingin membawa pengalaman industri ke ruang kelas.\n\nKolaborasi dapat berupa penyusunan modul, proyek mahasiswa, atau sesi kuliah tamu bersama praktisi.",
                'category' => 'Departemen',
                'days' => 14,
            ],
            [
                'title' => 'Tips menyusun perjanjian imersi yang jelas',
                'excerpt' => 'Perjanjian yang baik memuat peran, jadwal, target hasil, dan komitmen mentor–dosen.',
                'body' => "Sebelum program aktif, perjanjian imersi harus disepakati bersama mentor.\n\nPastikan peran, jadwal 8 minggu, target hasil, dan akses kerja sudah tertulis jelas agar perjalanan imersi lebih terukur.",
                'category' => 'Panduan',
                'days' => 18,
            ],
        ];

        foreach ($items as $item) {
            $slug = News::makeSlug($item['title']);

            News::updateOrCreate(
                ['title' => $item['title']],
                [
                    'slug' => $slug,
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'category' => $item['category'],
                    'status' => 'published',
                    'published_at' => now()->subDays($item['days']),
                    'author_id' => $admin?->id,
                ],
            );
        }
    }
}
