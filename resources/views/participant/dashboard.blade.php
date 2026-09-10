@extends('layouts.app')
@section('title', 'Ringkasan')
@section('content')
@php
    $next = ['Lengkapi profil kompetensi Anda.', route('participant.profile')];
    if ($participant?->study_program) {
        $next = ['Ajukan program ke unit bisnis yang relevan.', route('departments.index')];
    }
    if ($application && ! $program) {
        $next = match ($application->status) {
            'submitted' => ['Pendaftaran menunggu tinjauan admin.', route('participant.applications.show', $application)],
            'waiting_mentor' => ['Menunggu persetujuan mentor industri.', route('participant.applications.show', $application)],
            'waiting_admin' => ['Menunggu pengesahan akhir dari admin.', route('participant.applications.show', $application)],
            'revision' => ['Perbaiki form pendaftaran sesuai catatan reviewer.', route('participant.applications.edit', $application)],
            'rejected' => ['Pendaftaran ditolak. Ajukan program lain jika masih relevan.', route('participant.applications')],
            'approved' => ['Pendaftaran disetujui. Lanjutkan ke perjanjian imersi.', route('participant.agreement')],
            default => $next,
        };
    }
    if ($program) {
        $next = match ($program->status) {
            'draft', 'submitted' => ['Lengkapi dan ajukan Perjanjian Imersi Industri.', route('participant.agreement')],
            'revision' => ['Perbaiki perjanjian sesuai catatan mentor.', route('participant.agreement')],
            'agreed' => ['Menunggu program diaktifkan setelah perjanjian disepakati.', route('participant.agreement')],
            'active' => ['Isi logbook harian dan siapkan mentoring minggu ini.', route('participant.logbooks')],
            'completed' => ['Tentukan tindak lanjut kolaborasi setelah magang.', route('participant.collaboration')],
            default => $next,
        };
    }
    $timelineStep = 1;
    if ($program?->status === 'completed') {
        $timelineStep = 5;
    } elseif ($program?->status === 'active') {
        $week = $program->current_week ?? 1;
        $timelineStep = $week <= 2 ? 1 : ($week <= 4 ? 2 : ($week <= 7 ? 3 : 4));
    }
@endphp

{{-- Full-bleed hero ala carousel mitra --}}
<div class="-mx-5 -mt-6 mb-8">
    <section
        class="partner-stage overflow-hidden border-b border-line"
        x-data="{
            slides: @js($partners),
            index: 0,
            timer: null,
            get count() { return this.slides.length; },
            get current() { return this.slides[this.index] || null; },
            prev() { if (! this.count) return; this.index = (this.index - 1 + this.count) % this.count; },
            next() { if (! this.count) return; this.index = (this.index + 1) % this.count; },
            go(i) { this.index = i; },
            offset(i) {
                if (! this.count) return 0;
                let d = i - this.index;
                if (d > this.count / 2) d -= this.count;
                if (d < -this.count / 2) d += this.count;
                return d;
            },
            start() {
                this.stop();
                if (this.count < 2) return;
                this.timer = setInterval(() => this.next(), 5200);
            },
            stop() {
                if (this.timer) clearInterval(this.timer);
                this.timer = null;
            }
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
    >
        <div class="mx-auto max-w-7xl px-5 pb-10 pt-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Gelombang 2026</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white md:text-3xl">Pilih mitra imersi Anda</h1>
                    <p class="mt-1 max-w-xl text-sm text-white/75">Geser kartu untuk melihat unit bisnis mitra. Cover HD bisa ditambahkan nanti tanpa ubah kode.</p>
                </div>
                <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1 self-start rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white backdrop-blur hover:bg-white/25">
                    Semua mitra
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>

            <div class="relative mx-auto max-w-5xl px-10 md:px-14">
                <button type="button" @click="prev()" class="partner-nav left-0" aria-label="Sebelumnya">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button type="button" @click="next()" class="partner-nav right-0" aria-label="Berikutnya">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>

                <div class="relative mx-auto h-[220px] sm:h-[280px] md:h-[340px]">
                    <template x-for="(slide, i) in slides" :key="slide.id">
                        <a
                            :href="slide.url"
                            class="partner-card absolute inset-y-0 left-1/2 w-[78%] max-w-3xl overflow-hidden rounded-3xl shadow-2xl transition-all duration-500 ease-out sm:w-[70%]"
                            :style="`
                                transform: translateX(calc(-50% + ${offset(i) * 58}%)) scale(${offset(i) === 0 ? 1 : 0.86});
                                z-index: ${20 - Math.abs(offset(i))};
                                opacity: ${Math.abs(offset(i)) > 1 ? 0 : (offset(i) === 0 ? 1 : 0.55)};
                                pointer-events: ${offset(i) === 0 ? 'auto' : 'none'};
                            `"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br" :class="slide.gradient"></div>
                            <template x-if="slide.image">
                                <img :src="slide.image" :alt="slide.name" class="absolute inset-0 h-full w-full object-cover">
                            </template>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-secondary" x-text="slide.area"></p>
                                <h2 class="mt-1 text-2xl font-semibold text-white sm:text-3xl" x-text="slide.name"></h2>
                                <p class="mt-2 line-clamp-2 max-w-xl text-sm text-white/80" x-text="slide.description"></p>
                                <p class="mt-3 inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                    <span class="material-symbols-outlined text-[14px]">apartment</span>
                                    <span x-text="slide.units + ' penempatan terbuka'"></span>
                                </p>
                            </div>
                            <template x-if="! slide.image">
                                <div class="absolute right-5 top-5 rounded-full bg-black/25 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-white/80 backdrop-blur">
                                    Gambar menyusul
                                </div>
                            </template>
                        </a>
                    </template>

                    <template x-if="! count">
                        <div class="flex h-full items-center justify-center rounded-3xl border border-white/15 bg-white/10 text-sm text-white/80">
                            Belum ada mitra aktif.
                        </div>
                    </template>
                </div>

                <div class="mt-5 flex justify-center gap-2">
                    <template x-for="(slide, i) in slides" :key="'dot-'+slide.id">
                        <button
                            type="button"
                            class="h-2.5 rounded-full transition-all"
                            :class="i === index ? 'w-7 bg-secondary' : 'w-2.5 bg-white/35 hover:bg-white/60'"
                            @click="go(i)"
                            :aria-label="'Mitra ' + slide.name"
                        ></button>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white px-5 py-4 shadow-sm">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Unit Bisnis</p>
            <p class="mt-0.5 font-medium">{{ $program?->department?->name ?? 'Belum dipilih' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</p>
            <p class="mt-0.5 font-medium">{{ $program?->businessUnit?->name ?? 'Belum dipilih' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Mentor</p>
            <p class="mt-0.5 font-medium">{{ $program?->mentor?->user?->name ?? 'Belum ditugaskan' }}</p>
        </div>
        @if($program)
            <x-badge :status="$program->status" />
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <p class="max-w-xs text-sm text-muted">{{ $next[0] }}</p>
        <a href="{{ $next[1] }}" class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lanjutkan</a>
    </div>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['person', 'Profil', 'Lengkapi prodi & kompetensi', route('participant.profile')],
        ['send', 'Pendaftaran', 'Ajukan ke unit mitra', route('participant.applications')],
        ['edit_note', 'Buku Catatan', 'Isi refleksi harian', route('participant.logbooks')],
        ['calendar_month', 'Linimasa', 'Ikuti tonggak 8 minggu', route('participant.timeline')],
    ] as [$icon, $title, $copy, $url])
        <a href="{{ $url }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
            </span>
            <p class="mt-4 font-semibold">{{ $title }}</p>
            <p class="mt-1 text-sm text-muted">{{ $copy }}</p>
        </a>
    @endforeach
</div>

<x-framework-phases class="mt-10" />

<x-program-timeline :current="$timelineStep" :current-week="$program?->current_week" class="mt-14" />

<div class="mt-10 rounded-2xl border border-line bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-semibold">Notifikasi terbaru</h2>
        <a href="{{ route('participant.notifications') }}" class="text-sm font-semibold text-primary-dark">Lihat semua</a>
    </div>
    @forelse($notifications as $item)
        <a href="{{ $item->data['url'] ?? '#' }}" class="mt-3 block border-t border-line pt-3 text-sm">
            <b>{{ $item->data['title'] ?? 'Notifikasi' }}</b>
            <span class="text-muted"> — {{ $item->data['message'] ?? '' }}</span>
        </a>
    @empty
        <p class="mt-2 text-sm text-muted">Belum ada notifikasi.</p>
    @endforelse
</div>
@endsection
