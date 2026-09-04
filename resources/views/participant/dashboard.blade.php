@extends('layouts.app')
@section('title', 'Ringkasan')
@section('content')
@php
    $next = ['Lengkapi profil kompetensi Anda.', route('participant.profile')];
    if ($participant?->study_program) {
        $next = ['Ajukan program ke unit bisnis yang relevan.', route('participant.applications.create')];
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

<div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white px-5 py-4">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</p>
            <p class="mt-0.5 font-medium">{{ $program?->department?->name ?? 'Belum dipilih' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Unit Bisnis</p>
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

<x-framework-phases class="mt-10" />

<x-program-timeline :current="$timelineStep" :current-week="$program?->current_week" class="mt-14" />

<div class="mt-10 rounded-2xl border border-line bg-white p-5">
    <h2 class="font-semibold">Notifikasi terbaru</h2>
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
