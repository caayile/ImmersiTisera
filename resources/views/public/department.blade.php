@extends('layouts.public')
@section('title', $department->name)
@section('content')
@if($department->isDirectPlacement())
    <x-unit-detail :businessUnit="$department->primaryUnit()" :showHero="true" :showBack="true" :backToList="true" />
@else
@php
    $heroImage = $department->imageUrl()
        ?: ($hero->backgroundUrl() ?: asset('images/hero/campus.jpg'));

    $units = $department->businessUnits
        ->sortBy(fn ($unit) => [! $unit->isOpen(), $unit->name])
        ->values();

    $openCount  = $department->businessUnits->filter->isOpen()->count();
    $totalCount = $department->businessUnits->count();
@endphp

{{-- ===== SPLIT-SCREEN LAYOUT DENGAN BACKGROUND FULLSCREEN ===== --}}
<div class="dept-split" id="dept-split-root">

    {{-- Background foto fullscreen dari kanan membentang penuh ke kiri --}}
    <div
        class="dept-split__bg"
        id="dept-bg-layer"
        style="--dept-bg: url('{{ $heroImage }}')"
        aria-hidden="true"
    ></div>

    {{-- SISI KIRI — Panel putih transparan (tembus pandang ke background) --}}
    <div class="dept-split__left" id="dept-scroll-pane">

        {{-- Header: Judul & Informasi jumlah departemen --}}
        <div class="dept-split__header">
            <div class="flex items-center gap-3">
                <a href="{{ route('departments.index') }}" class="dept-back-btn" title="Kembali ke Daftar Unit">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink">Departemen</h1>
            </div>
            <div class="text-xs sm:text-sm font-medium text-ink/75">
                {{ $openCount }} lowongan dibuka - {{ $totalCount }} departemen
            </div>
        </div>

        {{-- Grid kartu unit departemen --}}
        <div class="dept-split__grid" id="dept-card-grid">
            @forelse($units as $unit)
                @php
                    $unitImage = $unit->imageUrl()
                        ?? ($department->imageUrl() ?: asset('images/hero/campus.jpg'));
                    $isOpen   = $unit->isOpen();
                    $filled   = (int) ($unit->active_applications_count ?? $unit->applications()->forQuota($unit)->count());
                    $quota    = \App\Models\BusinessUnit::MAX_APPLICANTS;
                    $isFull   = $filled >= $quota;
                @endphp
                <article
                    class="dept-unit-card"
                    data-unit-id="{{ $unit->id }}"
                    data-unit-name="{{ $unit->name }}"
                    data-unit-image="{{ $unitImage }}"
                    data-unit-dept="{{ $department->name }}"
                >
                    {{-- Foto Unit --}}
                    <div class="dept-unit-card__image-container">
                        <img
                            src="{{ $unitImage }}"
                            alt="{{ $unit->name }}"
                            class="dept-unit-card__img"
                            loading="lazy"
                        >
                        {{-- Badge nama unit / dept di kiri atas --}}
                        <div class="dept-unit-card__badge">
                            {{ $department->name }}
                        </div>
                    </div>

                    {{-- Glass overlay card di bagian bawah kartu --}}
                    <div class="dept-unit-card__glass">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-base text-ink tracking-tight truncate">{{ $unit->name }}</h3>
                            <p class="text-xs text-ink/80 line-clamp-2 mt-1 leading-relaxed">
                                {{ $unit->description ?: 'Unit ' . $unit->name . ' pada ' . $department->name . ' untuk magang dosen TSU.' }}
                            </p>
                        </div>
                        <div class="mt-3 flex items-center justify-between pt-1">
                            <span class="text-[11px] font-semibold {{ $isFull ? 'text-red-600' : ($isOpen ? 'text-emerald-700' : 'text-red-500') }}">
                                {{ $isFull ? 'Penuh' : ($isOpen ? 'Lowongan dibuka' : 'Ditutup') }}
                            </span>
                            <a href="{{ route('units.show', $unit) }}" class="dept-unit-card__btn">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-2 py-16 text-center text-ink/70 bg-white/40 backdrop-blur-md rounded-2xl border border-white/60">
                    <p class="font-medium">Belum ada departemen yang terdaftar.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Kapsul Scroll Navigasi di tengah perbatasan --}}
    <div class="dept-scroll-capsule" id="dept-scroll-capsule" aria-label="Navigasi Scroll">
        <button type="button" class="dept-scroll-capsule__btn" id="dept-arrow-up" aria-label="Scroll ke atas">
            <span class="material-symbols-outlined text-[18px]">keyboard_arrow_up</span>
        </button>
        <div class="dept-scroll-capsule__bar"></div>
        <button type="button" class="dept-scroll-capsule__btn" id="dept-arrow-down" aria-label="Scroll ke bawah">
            <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
        </button>
    </div>

    {{-- SISI KANAN — Halaman tetap transparan dengan teks info departemen di atas foto gedung --}}
    <div class="dept-split__right" id="dept-right-panel" aria-hidden="true">
        <div class="dept-split__right-content" id="dept-right-content">
            <p class="dept-right-eyebrow">{{ $department->area ?: 'PUBLISHING & CORPORATE' }}</p>
            <h2 class="dept-right-title">{{ $department->name }}</h2>
            <p class="dept-right-subtitle">{{ $department->subtitle ?: 'Tiga Serangkai Pustaka Mandiri' }}</p>
        </div>
    </div>

</div>

{{-- Script untuk scroll navigation --}}
<script>
(function () {
    var pane      = document.getElementById('dept-scroll-pane');
    var arrowUp   = document.getElementById('dept-arrow-up');
    var arrowDown = document.getElementById('dept-arrow-down');

    if (arrowUp && pane) {
        arrowUp.addEventListener('click', function () {
            pane.scrollBy({ top: -280, behavior: 'smooth' });
        });
    }

    if (arrowDown && pane) {
        arrowDown.addEventListener('click', function () {
            pane.scrollBy({ top: 280, behavior: 'smooth' });
        });
    }
})();
</script>
@endif
@endsection