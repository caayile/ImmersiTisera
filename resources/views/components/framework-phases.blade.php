@php
    $phases = [
        [
            'no' => '01',
            'fase' => 'FASE 1',
            'title' => 'IDENTIFIKASI',
            'copy' => 'Pemetaan kompetensi dosen ke kebutuhan industri secara linear: prodi → departemen → unit bisnis → mentor.',
            'icon' => 'rule',
            'meta' => 'Mesin Pencocokan Linear',
        ],
        [
            'no' => '02',
            'fase' => 'FASE 2',
            'title' => 'IMERSI',
            'copy' => 'Delapan minggu di unit bisnis: observasi alur kerja, ikut keputusan, dan berkontribusi pada pekerjaan nyata.',
            'icon' => 'domain',
            'meta' => '60 Hari di Industri',
        ],
        [
            'no' => '03',
            'fase' => 'FASE 3',
            'title' => 'INTERAKSI',
            'copy' => 'Pendampingan 30 menit setiap minggu plus buku catatan digital harian sebagai rekam jejak pembelajaran.',
            'icon' => 'forum',
            'meta' => 'Pemeriksaan Mingguan 30 Menit',
        ],
        [
            'no' => '04',
            'fase' => 'FASE 4',
            'title' => 'DAMPAK',
            'copy' => 'Satu hasil utama wajib: wawasan, SOP, prototipe, naskah kajian, atau rekomendasi untuk unit bisnis.',
            'icon' => 'fact_check',
            'meta' => '1 Hasil Utama',
        ],
        [
            'no' => '05',
            'fase' => 'FASE 5',
            'title' => 'INTEGRASI',
            'copy' => 'Kolaborasi lanjutan setelah magang: kuliah tamu, riset bersama, hingga pengembangan kurikulum.',
            'icon' => 'handshake',
            'meta' => 'Tingkat Alur 0–4',
        ],
    ];
@endphp
<section {{ $attributes->merge(['class' => 'text-center']) }}>
    <p class="inline-flex items-center gap-1.5 rounded-full bg-primary/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-primary-dark">
        <span class="material-symbols-outlined text-[15px]">route</span>
        Kerangka Imersi
    </p>
    <h2 class="mt-4 text-3xl font-semibold tracking-tight">Konsep Utama Program</h2>
    <p class="mx-auto mt-2 max-w-2xl text-sm text-muted">Lima fase terukur dari pencocokan kompetensi hingga kolaborasi lanjutan setelah imersi.</p>

    <div class="mt-8 grid gap-3 text-left sm:grid-cols-2 lg:grid-cols-5">
        @foreach($phases as $phase)
            <article class="flex min-h-[280px] flex-col rounded-2xl bg-[#eef4f1] p-5">
                <p class="text-2xl font-semibold tracking-tight text-primary-dark">{{ $phase['no'] }}</p>
                <p class="mt-4 text-[10px] font-semibold uppercase tracking-[0.18em] text-muted">{{ $phase['fase'] }}</p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight">{{ $phase['title'] }}</h3>
                <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ $phase['copy'] }}</p>
                <p class="mt-4 flex items-center gap-1.5 text-sm font-medium text-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">{{ $phase['icon'] }}</span>
                    {{ $phase['meta'] }}
                </p>
            </article>
        @endforeach
    </div>
</section>
