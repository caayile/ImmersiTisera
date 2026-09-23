@extends('layouts.public')
@section('title', $department->name)
@section('content')
@if($department->isDirectPlacement())
    <x-unit-detail :businessUnit="$department->primaryUnit()" :showHero="true" :showBack="true" :backToList="true" />
@else
@php
    $heroImage = $department->imageUrl()
        ?: ($hero->backgroundUrl() ?: asset('images/hero/campus.jpg'));
@endphp

<section class="bg-[#16352c] px-5 pt-16">
    <x-fill-image :src="$heroImage" :alt="$department->name" class="mx-auto h-64 w-full max-w-6xl rounded-3xl sm:h-80">
        <div class="pointer-events-none absolute inset-0 z-20 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
    </x-fill-image>
    <div class="relative mx-auto max-w-6xl pb-10 pt-8 text-white sm:pb-12">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">{{ $department->area ?: 'Mitra Imersi' }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">{{ $department->name }}</h1>
        @if($department->subtitle)
            <p class="mt-2 text-base text-white/80">{{ $department->subtitle }}</p>
        @endif
    </div>
</section>

<div class="mx-auto max-w-6xl px-5 py-12">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h2 class="text-2xl font-semibold">Departemen</h2>
        <p class="text-sm text-muted">
            {{ $department->businessUnits->where('status', 'open')->count() }} lowongan dibuka ·
            {{ $department->businessUnits->count() }} departemen
        </p>
    </div>
    <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $units = $department->businessUnits
                ->sortBy(fn ($unit) => [$unit->status !== 'open', $unit->name])
                ->values();
        @endphp
        @forelse($units as $unit)
            @php
                $unitImage = $unit->imageUrl()
                    ?? ($department->imageUrl() ?: asset('images/hero/campus.jpg'));
                $isOpen = $unit->status === 'open';
            @endphp
            <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                <a href="{{ route('units.show', $unit) }}">
                    <x-fill-image :src="$unitImage" :alt="$unit->name" class="h-52 w-full">
                        <span class="absolute bottom-3 left-4 z-20 rounded-md bg-black/55 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-white">{{ $department->name }}</span>
                    </x-fill-image>
                </a>
                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-lg font-semibold">{{ $unit->name }}</h3>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $isOpen ? 'bg-primary/12 text-primary-dark' : 'bg-red-50 text-red-600' }}">
                            {{ $isOpen ? 'Lowongan dibuka' : 'Lowongan ditutup' }}
                        </span>
                    </div>
                    <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ \Illuminate\Support\Str::limit($unit->description, 90) }}</p>
                    <a href="{{ route('units.show', $unit) }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#eef4f1] px-4 py-2.5 text-sm font-semibold text-ink hover:bg-primary hover:text-white">Lihat Detail</a>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted md:col-span-3">Belum ada departemen.</p>
        @endforelse
    </div>
</div>
@endif
@endsection