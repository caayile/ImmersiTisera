@extends('layouts.public')
@section('title', 'Beranda')
@section('content')
@php
    $capabilities = [
        ['person_add', 'Registrasi & Profil', 'Sinkronisasi data dosen, rekam publikasi, dan portofolio kompetensi untuk pencocokan yang akurat.', '01 / Sinkron Profil'],
        ['account_tree', 'Mesin Pencocokan Linear', 'Algoritma mencocokkan keahlian dosen dengan tantangan teknis unit bisnis secara terukur.', '02 / Pencocokan'],
        ['handshake', 'Perjanjian Imersi', 'Dokumen digital kesepakatan tiga pihak: dosen, kampus, dan industri sebelum program aktif.', '03 / Perjanjian'],
        ['calendar_month', 'Pemeriksaan 8 Minggu', 'Tonggak mingguan dari Temukan hingga Serahkan dengan target hasil yang jelas.', '04 / Tonggak'],
        ['edit_note', 'Buku Catatan Harian', 'Form refleksi harian untuk merekam aktivitas, pembelajaran, dan bukti kerja.', '05 / Catatan'],
        ['forum', 'Pendampingan Mingguan', 'Sesi 30 menit tiap minggu untuk menyelaraskan temuan, tugas, dan langkah berikutnya.', '06 / Mentor'],
        ['folder_managed', 'Repositori Bukti', 'Penyimpanan aman untuk artefak, dokumen, dan hasil kerja selama imersi.', '07 / Bukti'],
        ['diversity_3', 'Evaluasi Multi Perspektif', 'Penilaian 360 dari industri, mentor, dan capaian hasil kerja dosen.', '08 / Evaluasi'],
        ['trending_up', 'Alur Kolaborasi 0–4', 'Kerangka formal dari tutup hingga perluas kemitraan setelah magang.', '09 / Alur'],
        ['monitoring', 'Dasbor Eksekutif', 'Visibilitas real-time untuk pemantauan capaian program dan indikator kinerja.', '10 / Dasbor'],
    ];

    $unitMeta = [
        'Digital Business' => ['area' => 'Teknologi & Analitik', 'image' => 'from-[#16352c] to-[#3eaa84]'],
        'IT' => ['area' => 'Teknologi Informasi', 'image' => 'from-[#1e3a5f] to-[#5ec69d]'],
        'Center Of Excellence' => ['area' => 'Pembelajaran & Inovasi', 'image' => 'from-[#2f4a3c] to-[#7dd8b5]'],
    ];
@endphp

<section class="hero-grid">
    <div class="mx-auto grid max-w-6xl items-center gap-10 px-5 py-16 lg:grid-cols-2">
        <div>
            <p class="inline-flex rounded-full bg-black/25 px-3 py-1 text-xs font-medium text-white">GELOMBANG 2026 · SEKARANG DIBUKA</p>
            <h1 class="mt-5 text-4xl font-semibold leading-tight text-white md:text-5xl">Mulai imersi dari <span class="text-secondary">unit bisnis yang tepat</span> di sini.</h1>
            <p class="mt-4 max-w-xl text-sm leading-6 text-white/80">Imersi menghubungkan dosen TSU dengan dunia industri secara terukur: pencocokan, perjanjian, buku catatan, pendampingan, hasil kerja, dan kolaborasi lanjutan.</p>
            <form action="{{ route('departments.index') }}" class="mt-8 flex max-w-xl overflow-hidden rounded-full bg-white p-1.5">
                <input name="q" value="{{ request('q') }}" placeholder="Cari departemen atau unit bisnis" class="min-w-0 flex-1 px-4 text-sm outline-none">
                <button class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Cari</button>
            </form>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach([
                ['Wawasan Industri', 'Memahami alur kerja dan keputusan bisnis secara langsung.'],
                ['Mentor Profesional', 'Pendampingan 30 menit setiap minggu.'],
                ['Hasil Utama', 'Satu hasil kerja nyata untuk dosen dan unit bisnis.'],
                ['Kolaborasi Lanjutan', 'Kuliah tamu, riset, hingga kurikulum.'],
                ['Buku Catatan Harian', 'Rekaman aktivitas, pembelajaran, dan bukti kerja.'],
                ['Evaluasi Terukur', 'Pemahaman, relasi, hasil, dan dampak.'],
            ] as [$title, $copy])
                <article class="rounded-2xl bg-white p-4 shadow-lg">
                    <p class="font-semibold">{{ $title }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-bg py-16">
    <div class="mx-auto max-w-7xl px-5">
        <x-framework-phases />
        <x-program-timeline :current="1" class="mt-16" />
    </div>
</section>

<section class="bg-[#f4f8f6] py-16">
    <div class="mx-auto max-w-7xl px-5 text-center">
        <p class="inline-flex items-center gap-1.5 rounded-full bg-primary/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-primary-dark">
            <span class="material-symbols-outlined text-[15px]">architecture</span>
            Arsitektur Sistem
        </p>
        <h2 class="mt-4 text-3xl font-semibold tracking-tight">10 Kemampuan Inti Sistem</h2>
        <p class="mx-auto mt-3 max-w-3xl text-sm leading-6 text-muted">Didukung infrastruktur digital terpadu untuk memastikan akuntabilitas penuh, keterukuran capaian, dan perlindungan kerahasiaan korporat.</p>

        <div class="mt-10 grid gap-4 text-left sm:grid-cols-2 lg:grid-cols-5">
            @foreach($capabilities as [$icon, $title, $copy, $tag])
                <article class="flex min-h-[220px] flex-col rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                        <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                    </span>
                    <h3 class="mt-4 font-semibold leading-snug">{{ $title }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ $copy }}</p>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-primary-dark">{{ $tag }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-bg py-16">
    <div class="mx-auto max-w-7xl px-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Unit Bisnis Tersedia</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight">Departemen pilihan gelombang 2026</h2>
                <p class="mt-2 max-w-xl text-sm text-muted">Pilih unit kerja yang selaras dengan rumpun keilmuan dan bidang riset spesifik Anda.</p>
            </div>
            <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
                Lihat semua {{ $departments->count() }} departemen
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach($featuredUnits as $unit)
                @php
                    $meta = $unitMeta[$unit->name] ?? ['area' => $unit->department?->area ?? 'Unit Bisnis', 'image' => 'from-[#16352c] to-primary'];
                    $prodi = collect($unit->relevant_programs ?? [])->take(3)->implode(', ') ?: 'Semua prodi relevan';
                    $quota = max(2, count($unit->relevant_programs ?? []) + 1);
                @endphp
                <article class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                    <div class="relative h-44 bg-gradient-to-br {{ $meta['image'] }} p-4">
                        <span class="absolute left-4 top-4 rounded-md bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-ink">Gelombang terbuka</span>
                        <p class="absolute bottom-4 left-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/90">{{ $meta['area'] }}</p>
                    </div>
                    <div class="p-5">
                        <h3 class="text-xl font-semibold">{{ $unit->name }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ $unit->description }}</p>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted">Kuota dosen</dt>
                                <dd class="font-semibold">{{ $quota }} posisi</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="shrink-0 text-muted">Rekomendasi prodi</dt>
                                <dd class="text-right font-medium">{{ $prodi }}</dd>
                            </div>
                        </dl>
                        <a href="{{ route('units.show', $unit) }}" class="mt-5 block rounded-xl bg-[#eef4f1] px-4 py-3 text-center text-sm font-semibold text-ink hover:bg-primary hover:text-white">Detail unit bisnis</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10 overflow-hidden rounded-3xl bg-gradient-to-r from-[#dff5ea] via-[#eef8f3] to-[#d4efe6] p-6 md:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr] lg:items-center">
                <div>
                    <p class="inline-flex items-center gap-1.5 rounded-full bg-primary px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-white">
                        <span class="material-symbols-outlined text-[15px]">campaign</span>
                        Pendaftaran gelombang terbaru telah dibuka
                    </p>
                    <h3 class="mt-4 text-2xl font-semibold tracking-tight md:text-3xl">Siap menemukan lingkungan industri yang tepat untuk bidang keahlian Anda?</h3>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-muted">Imersi menuntun dosen dari pencocokan hingga kolaborasi lanjutan dengan sistem yang terukur dan transparan.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                    <a href="{{ route('departments.index') }}" class="inline-flex items-center justify-center gap-1 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">
                        Jelajahi semua departemen
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    <a href="{{ route('program.info') }}" class="inline-flex items-center justify-center gap-1 rounded-full bg-white px-5 py-3 text-sm font-semibold text-ink">
                        <span class="material-symbols-outlined text-[18px]">info</span>
                        Konsultasi program
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16">
    <div class="mx-auto max-w-7xl px-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Berita Terbaru</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight">Update program & kolaborasi</h2>
                <p class="mt-2 text-sm text-muted">Tiga berita paling baru dari Imersi.</p>
            </div>
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
                Lihat semua berita
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @forelse($latestNews as $item)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white">
                    <div class="flex h-32 items-end bg-gradient-to-br from-[#16352c] to-primary p-4">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">{{ $item->category }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-semibold text-primary-dark">{{ $item->published_at?->translatedFormat('d M Y') }}</p>
                        <h3 class="mt-2 text-lg font-semibold leading-snug">{{ $item->title }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ $item->excerpt }}</p>
                        <a href="{{ route('news.show', $item) }}" class="mt-4 text-sm font-semibold text-primary-dark">Baca selengkapnya →</a>
                    </div>
                </article>
            @empty
                <p class="text-sm text-muted md:col-span-3">Belum ada berita terbit.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
