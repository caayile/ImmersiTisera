@extends('layouts.app')
@section('title', 'Program Saya')
@section('content')
<h1 class="text-2xl font-semibold">Program Saya</h1>
@unless($program)
    <x-empty class="mt-6" title="Belum ada program" :action="route('participant.applications.create')" label="Daftar program">Pengajuan Anda akan menjadi program setelah matching disetujui.</x-empty>
@else
<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <article class="rounded-2xl border border-line bg-white p-5 lg:col-span-2">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">{{ $program->businessUnit->name }}</h2>
            <x-badge :status="$program->status" />
        </div>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-muted">Department</dt><dd>{{ $program->department->name }}</dd></div>
            <div><dt class="text-muted">Mentor</dt><dd>{{ $program->mentor->user->name }}</dd></div>
            <div><dt class="text-muted">Periode</dt><dd>{{ $program->start_date?->format('d M Y') ?? '—' }} – {{ $program->end_date?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-muted">Progress</dt><dd>{{ $program->progress }}% · Minggu {{ $program->current_week }}</dd></div>
        </dl>
    </article>
    <article class="rounded-2xl border border-line bg-white p-5 text-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Jejak program</p>
        <ul class="mt-3 space-y-2">
            <li>Agreement: {{ strtoupper($program->agreement?->status ?? 'draft') }}</li>
            <li>Logbook: {{ $program->logbooks->count() }}</li>
            <li>Mentoring: {{ $program->mentorSessions->count() }}</li>
            <li>Output: {{ $program->outputs->count() }}</li>
            <li>Evaluasi: {{ $program->evaluations->count() }}</li>
            <li>Kolaborasi: {{ $program->collaboration?->level ?? 0 }}</li>
        </ul>
    </article>
</div>
@endif
@endsection
