@extends('layouts.app')
@section('title', 'Dashboard Program')
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
            'revision' => ['Perbaiki formulir pendaftaran sesuai catatan peninjau.', route('participant.applications.edit', $application)],
            'rejected' => ['Pendaftaran ditolak. Ajukan program lain jika masih relevan.', route('participant.applications')],
            'approved' => ['Pendaftaran disetujui. Lanjutkan ke perjanjian magang dosen.', route('participant.agreement')],
            default => $next,
        };
    }
    if ($program) {
        $next = match ($program->status) {
            'draft', 'submitted' => ['Lengkapi dan ajukan Perjanjian Magang Dosen.', route('participant.agreement')],
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

@if($program?->mentor?->user)
    @php
        $mentorPhone = preg_replace('/\D+/', '', (string) $program->mentor->user->phone);
        if (str_starts_with($mentorPhone, '0')) {
            $mentorPhone = '62'.substr($mentorPhone, 1);
        }
        $mentorAvatar = $program->mentor->user->avatarUrl();
        $mentorInitials = collect(preg_split('/\s+/', trim($program->mentor->user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    @endphp
    <section class="mt-5 flex flex-col gap-4 rounded-2xl border border-[#cde8dc] bg-[#f1fbf6] p-5 sm:flex-row sm:items-center sm:justify-between" x-data="{ mentorOpen: false }">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-dark">Mentor</p>
            <h2 class="mt-1 text-lg font-semibold">{{ $program->mentor->user->name }}</h2>
            <p class="mt-1 text-sm text-muted">Mentor industri untuk program magang Anda. Silakan berkoordinasi langsung dengan mentor.</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <button type="button" @click="mentorOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-primary/30 bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
                <span class="material-symbols-outlined text-[19px]">person</span>
                Lihat Profil
            </button>
            @if($mentorPhone)
                <a href="https://wa.me/{{ $mentorPhone }}" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#159447] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#107c3a]">
                    <span class="material-symbols-outlined text-[19px]">chat</span>
                    Buka WhatsApp
                </a>
            @endif
        </div>

        <div x-show="mentorOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="mentorOpen && 'flex'" @click.self="mentorOpen = false" @keydown.escape.window="mentorOpen = false" role="dialog" aria-modal="true" aria-label="Profil mentor">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-xl">
                <div class="hero-grid px-6 py-6">
                    <div class="flex items-center gap-4">
                        @if($mentorAvatar)
                            <img src="{{ $mentorAvatar }}" alt="" width="64" height="64" class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-sm" referrerpolicy="no-referrer">
                        @else
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white text-xl font-semibold text-primary-dark shadow-sm">{{ $mentorInitials }}</span>
                        @endif
                        <div class="min-w-0 text-white">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-secondary">Mentor Industri</p>
                            <h3 class="mt-0.5 truncate text-xl font-semibold">{{ $program->mentor->user->name }}</h3>
                            <p class="mt-0.5 truncate text-sm text-white/75">{{ $program->mentor->user->email }}</p>
                        </div>
                        <button type="button" @click="mentorOpen = false" class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25" aria-label="Tutup">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                </div>
                <dl class="grid gap-3 p-6 text-sm sm:grid-cols-2">
                    <div class="rounded-xl bg-bg px-3 py-2.5">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">NIK</dt>
                        <dd class="mt-0.5 font-medium">{{ $program->mentor->nik ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-bg px-3 py-2.5">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Telepon</dt>
                        <dd class="mt-0.5 font-medium">{{ $program->mentor->user->phone ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-bg px-3 py-2.5">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Posisi</dt>
                        <dd class="mt-0.5 font-medium">{{ $program->mentor->position ?: 'Mentor Industri' }}</dd>
                    </div>
                    <div class="rounded-xl bg-bg px-3 py-2.5">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Unit Bisnis</dt>
                        <dd class="mt-0.5 font-medium">{{ $program->mentor->department?->name ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-bg px-3 py-2.5 sm:col-span-2">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Departemen</dt>
                        <dd class="mt-0.5 font-medium">{{ $program->mentor->businessUnit?->name ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-bg px-3 py-2.5 sm:col-span-2">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Keahlian</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @forelse($program->mentor->expertise ?? [] as $skill)
                                <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-medium text-primary-dark">{{ $skill }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
@endif

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['person', 'Profil', 'Lengkapi prodi & kompetensi', route('participant.profile')],
        ['send', 'Pendaftaran', 'Ajukan ke unit mitra', route('participant.applications')],
        ['edit_note', 'Logbook', 'Isi refleksi harian', route('participant.logbooks')],
        ['fact_check', 'Checkpoint', '4 fase: Orientasi · Observasi · Kolaborasi · Laporan/Hasil', route('participant.timeline')],
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

<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['work_history', 'Total Magang', $stats['programs'] ?? 0, 'Siklus', route('participant.program')],
        ['edit_note', 'Entri Logbook', $stats['logbooks'] ?? 0, 'Entri', route('participant.logbooks')],
        ['forum', 'Sesi Mentoring', $stats['mentorings'] ?? 0, 'Sesi', route('participant.mentoring')],
        ['folder_special', 'Hasil', $stats['outputs'] ?? 0, 'Berkas', route('participant.outputs')],
    ] as [$icon, $title, $count, $unit, $url])
        <a href="{{ $url }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
            </span>
            <p class="mt-4 text-3xl font-semibold">{{ $count }}</p>
            <p class="mt-1 text-sm font-semibold">{{ $title }}</p>
            <p class="text-xs text-muted">{{ $unit }}</p>
        </a>
    @endforeach
</div>
@endsection
