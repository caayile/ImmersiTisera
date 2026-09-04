@props(['current' => 1, 'currentWeek' => null])
@php
    $steps = [
        [
            'week' => 'MINGGU 1 – 2',
            'tag' => 'TEMUKAN',
            'title' => 'Orientasi & Observasi Bisnis',
            'copy' => 'Memahami budaya unit, alur kerja, dan peran tim sebelum mulai berkontribusi.',
            'items' => ['Orientasi budaya & tim unit bisnis', 'Penyelarasan harapan bersama mentor', 'Akses sistem, data, dan ruang kerja'],
            'output' => 'Catatan Wawasan Industri',
            'icon' => 'description',
        ],
        [
            'week' => 'MINGGU 3 – 4',
            'tag' => 'PAHAMI',
            'title' => 'Eksplorasi Masalah & Peluang',
            'copy' => 'Mengurai hambatan, mendengar pemangku kepentingan, dan merumuskan hipotesis yang bisa diuji.',
            'items' => ['Analisis hambatan proses bisnis', 'Wawancara pemangku kepentingan terkait', 'Perumusan hipotesis solusi'],
            'output' => 'Pernyataan Masalah Tervalidasi',
            'icon' => 'analytics',
        ],
        [
            'week' => 'MINGGU 5 – 7',
            'tag' => 'KONTRIBUSI',
            'title' => 'Riset Terapan & Prototipe',
            'copy' => 'Membangun solusi, divalidasi tiap minggu, lalu diperbaiki menjadi hasil kerja.',
            'items' => ['Pengembangan solusi / prototipe', 'Validasi mingguan dengan mentor', 'Perbaikan hasil kerja utama'],
            'output' => 'Draf Solusi / Prototipe',
            'icon' => 'developer_board',
        ],
        [
            'week' => 'MINGGU 8',
            'tag' => 'SERAHKAN',
            'title' => 'Finalisasi & Presentasi Unit',
            'copy' => 'Menyelesaikan hasil, presentasi ke unit, dan menutup program dengan evaluasi menyeluruh.',
            'items' => ['Presentasi kepada pimpinan unit bisnis', 'Serah terima berkas & dokumentasi akhir', 'Evaluasi menyeluruh dosen × mentor'],
            'output' => 'Hasil Utama & Nota Kesepahaman',
            'icon' => 'workspace_premium',
        ],
    ];
    $weeks = [
        ['n' => 1, 'phase' => 'TEMUKAN', 'title' => 'Pengenalan', 'copy' => 'Budaya unit, akses sistem, pembukaan bersama mentor.'],
        ['n' => 2, 'phase' => 'TEMUKAN', 'title' => 'Observasi', 'copy' => 'Alur kerja, keputusan bisnis, draf wawasan.'],
        ['n' => 3, 'phase' => 'PAHAMI', 'title' => 'Pemangku kepentingan', 'copy' => 'Peta aktor, hambatan, kebutuhan unit.'],
        ['n' => 4, 'phase' => 'PAHAMI', 'title' => 'Masalah', 'copy' => 'Validasi masalah & pernyataan masalah.'],
        ['n' => 5, 'phase' => 'KONTRIBUSI', 'title' => 'Coba', 'copy' => 'Percobaan awal dan uji pendekatan.'],
        ['n' => 6, 'phase' => 'KONTRIBUSI', 'title' => 'Uji', 'copy' => 'Validasi mingguan, umpan balik mentor.'],
        ['n' => 7, 'phase' => 'KONTRIBUSI', 'title' => 'Kembangkan', 'copy' => 'Perbaikan draf solusi / prototipe.'],
        ['n' => 8, 'phase' => 'SERAHKAN', 'title' => 'Presentasi', 'copy' => 'Finalisasi, presentasi, evaluasi menyeluruh.'],
    ];
@endphp
<section {{ $attributes }} x-data="{ mode: 'milestone' }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="inline-flex rounded-full bg-primary/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-primary-dark">Perjalanan 60 Hari</p>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight md:text-3xl">Linimasa Imersi Terstruktur 8 Minggu</h2>
            <p class="mt-2 max-w-xl text-sm text-muted">Setiap tahap punya pemeriksaan, aktivitas, dan target hasil yang harus diselesaikan.</p>
        </div>
        <div class="inline-flex self-start rounded-full border border-line bg-white p-1 text-sm">
            <button type="button" class="rounded-full px-4 py-1.5 font-medium transition" :class="mode === 'milestone' ? 'bg-primary text-white' : 'text-muted hover:text-ink'" @click="mode = 'milestone'">Mode Tonggak</button>
            <button type="button" class="rounded-full px-4 py-1.5 font-medium transition" :class="mode === 'matrix' ? 'bg-primary text-white' : 'text-muted hover:text-ink'" @click="mode = 'matrix'">Matriks Mingguan</button>
        </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4" x-show="mode === 'milestone'">
        @foreach($steps as $index => $step)
            @php $n = $index + 1; $done = $n < $current; $active = $n === $current; @endphp
            <article class="flex flex-col rounded-2xl border bg-white p-5 {{ $active ? 'border-primary shadow-[0_0_0_4px_rgba(94,198,157,0.12)]' : 'border-line' }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">{{ $step['week'] }}</p>
                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-primary-dark">{{ $step['tag'] }}</span>
                </div>
                <h3 class="mt-4 text-lg font-semibold leading-snug">{{ $step['title'] }}</h3>
                <p class="mt-2 text-sm leading-6 text-muted">{{ $step['copy'] }}</p>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($step['items'] as $item)
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined mt-0.5 text-[18px] text-primary-dark">check_circle</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 mt-auto flex items-center justify-between gap-3 rounded-xl border border-line bg-[#f6f8f7] px-3 py-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Target Hasil</p>
                        <p class="mt-0.5 text-sm font-medium">{{ $step['output'] }}</p>
                    </div>
                    <span class="material-symbols-outlined text-[22px] text-primary-dark">{{ $step['icon'] }}</span>
                </div>
                @if($done)
                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Selesai</p>
                @elseif($active)
                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-amber-700">Berlangsung</p>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-8 overflow-x-auto pb-2" x-show="mode === 'matrix'" x-cloak>
        <div class="grid min-w-[880px] grid-cols-8 gap-3">
            @foreach($weeks as $week)
                @php $activeWeek = $currentWeek && (int) $currentWeek === $week['n']; @endphp
                <article class="rounded-2xl border bg-white p-4 {{ $activeWeek ? 'border-primary shadow-[0_0_0_4px_rgba(94,198,157,0.12)]' : 'border-line' }}">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Minggu {{ $week['n'] }}</p>
                    <span class="mt-2 inline-flex rounded-full bg-primary/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-primary-dark">{{ $week['phase'] }}</span>
                    <h3 class="mt-3 font-semibold">{{ $week['title'] }}</h3>
                    <p class="mt-2 text-sm leading-5 text-muted">{{ $week['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
