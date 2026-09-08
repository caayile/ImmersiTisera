@extends('layouts.public')
@section('title', $department->name)
@section('content')
@php
    $heroImage = $department->imageUrl()
        ?: ($hero->backgroundUrl() ?: asset('images/hero/campus.jpg'));
@endphp

<section class="relative overflow-hidden bg-gradient-to-br from-[#16352c] to-primary">
    <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/55 to-black/30"></div>
    <div class="relative mx-auto max-w-6xl px-5 pb-16 pt-24 text-white md:pb-20 md:pt-28">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">{{ $department->area ?: 'Mitra Imersi' }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">{{ $department->name }}</h1>
        @if($department->subtitle)
            <p class="mt-2 text-base text-white/80">{{ $department->subtitle }}</p>
        @endif
    </div>
</section>

<div class="mx-auto max-w-6xl px-5 py-12">
    @if($department->isDirectPlacement())
        <div class="mt-10 rounded-2xl border border-line bg-white p-6">
            <h2 class="text-xl font-semibold">Penempatan langsung</h2>
            <p class="mt-2 text-sm text-muted">Unit bisnis ini belum memiliki departemen terpisah. Pengajuan imersi diajukan langsung ke unit bisnis.</p>
            @auth
                <a href="{{ route('participant.applications.create', ['unit' => $department->primaryUnit()?->id]) }}" class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Ajukan ke unit bisnis ini</a>
            @else
                <a href="{{ route('login') }}" class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Masuk untuk mengajukan</a>
            @endauth
        </div>
    @else
        <h2 class="mt-12 text-2xl font-semibold">Departemen</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($department->businessUnits as $unit)
                @php
                    $unitImage = $unit->imageUrl()
                        ?? ($department->imageUrl() ?: asset('images/hero/campus.jpg'));
                @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                    <a href="{{ route('units.show', $unit) }}" class="relative block h-44 overflow-hidden bg-gradient-to-br from-[#16352c] to-primary">
                        <img src="{{ $unitImage }}" alt="{{ $unit->name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
                        <span class="absolute bottom-3 left-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/90">{{ $department->name }}</span>
                    </a>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-lg font-semibold">{{ $unit->name }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ \Illuminate\Support\Str::limit($unit->description, 90) }}</p>
                        <a href="{{ route('units.show', $unit) }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#eef4f1] px-4 py-2.5 text-sm font-semibold text-ink hover:bg-primary hover:text-white">Lihat Detail</a>
                    </div>
                </article>
            @empty
                <p class="text-sm text-muted md:col-span-3">Belum ada departemen.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection