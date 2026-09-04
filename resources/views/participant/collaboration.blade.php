@extends('layouts.app')
@section('title', 'Kolaborasi')
@section('content')
<h1 class="text-2xl font-semibold">After-Magang Collaboration</h1>
@unless($program)
    <x-empty class="mt-6" title="Pipeline kolaborasi belum ada" />
@else
@php $item = $program->collaboration; $levels = \App\Support\Status::COLLABORATION_LEVELS; @endphp
<article class="mt-6 rounded-2xl border border-line bg-white p-6">
    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Level saat ini</p>
    <p class="mt-2 text-2xl font-semibold">{{ $levels[$item->level ?? 0] }}</p>
    <p class="mt-2 text-sm">{{ $item?->collaboration_type }}</p>
    <p class="mt-2 text-sm text-muted">{{ $item?->description ?? 'Belum ada tindak lanjut.' }}</p>
    <p class="mt-4 text-sm"><b>Next action:</b> {{ $item?->next_action ?? '—' }}</p>
    <p class="text-sm"><b>PIC:</b> {{ $item?->responsible_person ?? '—' }}</p>
    <p class="text-sm"><b>Target:</b> {{ $item?->target_date?->format('d M Y') ?? '—' }}</p>
</article>
@endif
@endsection
