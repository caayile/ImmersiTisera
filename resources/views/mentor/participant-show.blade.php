@extends('layouts.app')
@section('title', $program->participant->user->name)
@section('content')
<p class="text-sm text-primary-dark">Monitoring peserta</p>
<h1 class="mt-1 text-2xl font-semibold">{{ $program->participant->user->name }}</h1>
<div class="mt-4 flex flex-wrap gap-3 text-sm">
    <x-badge :status="$program->status" />
    <span>{{ $program->businessUnit->name }}</span>
    <span>Week {{ $program->current_week }}</span>
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <article class="rounded-2xl border border-line bg-white p-5 text-sm">
        <h2 class="font-semibold">Agreement</h2>
        <p class="mt-2">{{ $program->agreement?->objective }}</p>
        <x-badge class="mt-3" :status="$program->agreement?->status ?? 'draft'" />
    </article>
    <article class="rounded-2xl border border-line bg-white p-5 text-sm">
        <h2 class="font-semibold">Logbook terbaru</h2>
        @foreach($program->logbooks->take(3) as $log)
            <p class="mt-2">{{ $log->date->format('d M') }} · {{ $log->activity }} · {{ $log->status }}</p>
        @endforeach
    </article>
</div>
@endsection
