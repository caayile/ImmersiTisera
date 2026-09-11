@extends('layouts.public')
@section('title', $businessUnit->name)
@section('content')
@php
    $image = $businessUnit->imageUrl()
        ?? ($businessUnit->department?->imageUrl() ?: asset('images/hero/campus.jpg'));
    $dept = $businessUnit->department;
    $location = $dept?->area ?: 'Solo, Indonesia';

    $mapUrl = null;
    if ($dept && $dept->map_url) {
        $mapLink = trim($dept->map_url);
        if (str_contains($mapLink, 'output=embed')) {
            $mapUrl = $mapLink;
        } elseif (preg_match('/@(-?[\d.]+),(-?[\d.]+)/', $mapLink, $m)) {
            $mapUrl = 'https://maps.google.com/maps?q='.$m[1].','.$m[2].'&z=17&output=embed';
        }
    }
    $mapUrl = $mapUrl ?: 'https://maps.google.com/maps?q=' . urlencode(($dept?->name ?: 'TSU') . ' ' . $location) . '&t=&z=14&output=embed';
    $isOpen = $businessUnit->isOpen();
    $hasWindow = $businessUnit->isScheduled();
    $deadline = $businessUnit->registration_deadline
        ? \Carbon\Carbon::parse($businessUnit->registration_deadline)->locale('id')->translatedFormat('j F Y, H.i')
        : null;
    $start = $businessUnit->registration_start
        ? \Carbon\Carbon::parse($businessUnit->registration_start)->locale('id')->translatedFormat('j F Y, H.i')
        : null;
    $isRegistered = auth()->check() && auth()->user()->participant
        && auth()->user()->participant
            ->applications()
            ->where('business_unit_id', $businessUnit->id)
            ->exists();
@endphp

<div class="mx-auto max-w-6xl px-5 py-12">
    <div class="relative h-52 overflow-hidden rounded-3xl bg-gradient-to-br from-[#16352c] to-primary sm:h-64">
        <img src="{{ $image }}" alt="{{ $businessUnit->name }}" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-secondary">{{ $dept?->name ?: 'Unit Bisnis' }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-white sm:text-3xl">{{ $businessUnit->name }}</h1>
        </div>
    </div>

    <div class="mt-8 gap-8 lg:grid lg:grid-cols-[1fr_400px] lg:items-start">
        {{-- ======== KOLOM KIRI: SATU CARD UTAMA ======== --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
            <div class="space-y-6">
                <section>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/12 text-primary-dark">
                            <span class="material-symbols-outlined text-[20px]">menu_book</span>
                        </span>
                        <h2 class="text-xl font-bold tracking-tight">Tentang Program</h2>
                    </div>
                    <div class="rich-content mt-4">{!! $businessUnit->description !!}</div>
                    @if($businessUnit->function)
                        <div class="rich-content mt-4 rounded-lg bg-emerald-50/50 p-3">{!! $businessUnit->function !!}</div>
                    @endif
                </section>

                @if($businessUnit->work_done)
                    <section class="border-t border-gray-100 pt-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/12 text-primary-dark">
                                <span class="material-symbols-outlined text-[20px]">build</span>
                            </span>
                            <h2 class="text-xl font-bold tracking-tight">Yang Dikerjakan</h2>
                        </div>
                        <div class="rich-content mt-4">{!! $businessUnit->work_done !!}</div>
                    </section>
                @endif

                @if($businessUnit->example_activities)
                    <section class="border-t border-gray-100 pt-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/12 text-primary-dark">
                                <span class="material-symbols-outlined text-[20px]">assignment</span>
                            </span>
                            <h2 class="text-xl font-bold tracking-tight">Detail Aktivitas</h2>
                        </div>
                        <div class="rich-content mt-4">{!! $businessUnit->example_activities !!}</div>
                    </section>
                @endif

                @if($businessUnit->requirements || !empty($businessUnit->relevant_programs))
                    <section class="border-t border-gray-100 pt-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/12 text-primary-dark">
                                <span class="material-symbols-outlined text-[20px]">checklist</span>
                            </span>
                            <h2 class="text-xl font-bold tracking-tight">Persyaratan & Silabus</h2>
                        </div>
                        @if($businessUnit->requirements)
                            <div class="rich-content mt-4">{!! $businessUnit->requirements !!}</div>
                        @endif
                        @if(!empty($businessUnit->relevant_programs))
                            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.14em] text-muted">Prodi relevan</p>
                            <ul class="mt-3 space-y-2.5">
                                @foreach($businessUnit->relevant_programs as $program)
                                    <li class="flex items-center gap-2.5 text-sm text-ink">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-white">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                        </span>
                                        {{ $program }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif
            </div>
        </div>

        {{-- ======== KOLOM KANAN: SIDEBAR ======== --}}
        <aside class="mt-8 space-y-4 lg:sticky lg:top-24 lg:mt-0">
            @if($dept)
                <a href="{{ route('departments.show', $dept) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-muted shadow-sm transition hover:border-primary hover:text-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke departemen lainnya
                </a>
            @else
                <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-muted shadow-sm transition hover:border-primary hover:text-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke departemen lainnya
                </a>
            @endif

            {{-- CARD 1: ACTION BOX PENDAFTARAN --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_10px_30px_rgba(31,42,40,0.08)] md:p-8">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $isOpen ? 'bg-primary/12 text-primary-dark' : ($hasWindow ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                    <span class="material-symbols-outlined text-[16px]">{{ $isOpen ? 'lock_open' : ($hasWindow ? 'lock' : 'schedule') }}</span>
                    {{ $isOpen ? 'Terbuka' : ($hasWindow ? 'Tutup' : 'Akan diumumkan') }}
                </span>
                @if($isOpen)
                    <p class="mt-4 text-sm text-ink">Pendaftaran dibuka sampai</p>
                    <p class="mt-1 text-xl font-bold text-ink">{{ $deadline }}</p>
                @elseif($hasWindow)
                    <p class="mt-4 text-sm text-ink">Pendaftaran dibuka sampai</p>
                    <p class="mt-1 text-xl font-bold text-ink">{{ $deadline }}</p>
                @else
                    <p class="mt-4 text-sm text-ink">Jadwal pendaftaran</p>
                    <p class="mt-1 text-xl font-bold text-ink">Akan diumumkan</p>
                @endif
                @if($start && !$businessUnit->isOpen() && $hasWindow && $businessUnit->registration_start > now())
                    <p class="mt-2 text-xs text-muted">Belum dibuka — pemberitahuan aktif sejak <b>{{ $start }}</b></p>
                @endif

                @if($isRegistered)
                    <button type="button" disabled class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-[#e4eee9] px-5 py-3.5 text-sm font-semibold text-muted">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        Sudah Terdaftar
                    </button>
                    <p class="mt-3 text-center text-[11px] text-muted">Anda telah mendaftar pada unit ini.</p>
                @elseif($isOpen)
                    <a href="{{ route('participant.applications.create', ['unit' => $businessUnit->id]) }}" class="mt-6 flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-3.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(94,198,157,0.4)] transition hover:bg-primary-dark">
                        Daftar Program
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    <p class="mt-3 text-center text-[11px] text-muted">Daftar untuk memulai proses imersi di unit ini.</p>
                @elseif($hasWindow)
                    <button type="button" disabled class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-[#e4eee9] px-5 py-3.5 text-sm font-semibold text-muted">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        Pendaftaran ditutup
                    </button>
                    <p class="mt-3 text-center text-[11px] text-muted">Masa pendaftaran unit ini telah berakhir.</p>
                @else
                    <button type="button" disabled class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-[#e4eee9] px-5 py-3.5 text-sm font-semibold text-muted">
                        <span class="material-symbols-outlined text-[18px]">schedule</span>
                        Segera dibuka
                    </button>
                    <p class="mt-3 text-center text-[11px] text-muted">Jadwal pendaftaran unit ini akan diumumkan oleh admin.</p>
                @endif
            </div>

            {{-- MAP LOKASI --}}
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-ink">
                        <span class="material-symbols-outlined text-[18px] text-primary-dark">location_on</span>
                        Lokasi Magang
                    </h3>
                    <span class="text-xs text-muted">{{ $location }}</span>
                </div>
                <div class="h-48 overflow-hidden lg:h-56">
                    <iframe
                        src="{{ $mapUrl }}"
                        title="Lokasi {{ $dept?->name ?: 'unit bisnis' }}"
                        class="h-full w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection