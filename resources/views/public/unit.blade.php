@extends('layouts.public')
@section('title', $businessUnit->name)
@section('content')
@php
    $image = $businessUnit->imageUrl()
        ?? ($businessUnit->department?->imageUrl() ?: asset('images/hero/campus.jpg'));
@endphp

<div class="mx-auto max-w-5xl px-5 py-12">
    <div class="relative h-52 overflow-hidden rounded-3xl bg-gradient-to-br from-[#16352c] to-primary sm:h-64">
        <img src="{{ $image }}" alt="{{ $businessUnit->name }}" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-secondary">{{ $businessUnit->department?->name ?: 'Unit Bisnis' }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-white sm:text-3xl">{{ $businessUnit->name }}</h1>
        </div>
    </div>

    <div class="mt-6 space-y-3 rounded-2xl border border-line bg-white p-6 text-sm">
        <p>{{ $businessUnit->description }}</p>
        <p><b>Yang dikerjakan:</b> {{ $businessUnit->work_done }}</p>
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