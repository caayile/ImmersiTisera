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
    @endphp
    <section class="mt-5 flex flex-col gap-4 rounded-2xl border border-[#cde8dc] bg-[#f1fbf6] p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-dark">Kontak pendamping</p>
            <h2 class="mt-1 text-lg font-semibold">{{ $program->mentor->user->name }}</h2>
            <p class="mt-1 text-sm text-muted">Mentor industri untuk program Anda. Silakan berkoordinasi langsung di luar sistem.</p>
        </div>
        @if($mentorPhone)
            <a href="https://wa.me/{{ $mentorPhone }}" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#159447] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#107c3a]">
                <span class="material-symbols-outlined text-[19px]">chat</span>
                Buka WhatsApp
            </a>
        @endif
    </section>
@endif

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['person', 'Profil', 'Lengkapi prodi & kompetensi', route('participant.profile')],
        ['send', 'Pendaftaran', 'Ajukan ke unit mitra', route('participant.applications')],
        ['edit_note', 'Logbook', 'Isi refleksi harian', route('participant.logbooks')],
        ['fact_check', 'Checkpoint', 'Isi laporan pada minggu 2, 4, 6, dan 8', route('participant.timeline')],
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
