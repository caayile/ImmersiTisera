@extends('layouts.public')
@section('title', $businessUnit->name)
@section('content')
<div class="mx-auto max-w-4xl px-5 py-12">
    <p class="text-sm text-primary-dark">Unit Bisnis: {{ $businessUnit->department->name }}</p>
    <h1 class="mt-1 text-3xl font-semibold">{{ $businessUnit->name }}</h1>
    <div class="mt-6 space-y-3 rounded-2xl border border-line bg-white p-6 text-sm">
        <p>{{ $businessUnit->description }}</p>
        <p><b>Departemen:</b> {{ $businessUnit->name }}</p>
        <p><b>Yang dikerjakan:</b> {{ $businessUnit->work_done }}</p>
        <p><b>Fungsi:</b> {{ $businessUnit->function }}</p>
        <p><b>Contoh aktivitas:</b> {{ $businessUnit->example_activities }}</p>
        <p><b>Persyaratan:</b> {{ $businessUnit->requirements }}</p>
        <p><b>Prodi relevan:</b> {{ implode(', ', $businessUnit->relevant_programs ?? []) }}</p>
        <p><b>Periode:</b> {{ $businessUnit->period }}</p>
        <p><b>Status:</b> {{ $businessUnit->status }}</p>
        <p><b>Mentor:</b>
            {{ $businessUnit->mentors->pluck('user.name')->filter()->join(', ') ?: 'Akan ditugaskan' }}
        </p>
    </div>
    <a href="{{ auth()->check() ? route('participant.applications.create', ['unit' => $businessUnit->id]) : route('register') }}" class="mt-6 inline-block rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white">Daftar Program</a>
</div>
@endsection
