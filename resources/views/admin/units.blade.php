@extends('layouts.app')
@section('title', 'Departemen')
@section('content')
<h1 class="text-2xl font-semibold">Departemen</h1>
<p class="mt-1 text-sm text-muted">Pilih unit bisnis untuk mengelola departemen, program, pendaftaran, dan lokasi di dalamnya.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    @forelse($departments as $department)
        <div class="flex items-center gap-4 border-b border-line px-5 py-4 last:border-0 transition hover:bg-bg/50">
            @if($department->imageUrl())
                <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-14 w-20 shrink-0 rounded-lg object-cover">
            @else
                <span class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-primary/12 text-primary-dark">
                    <span class="material-symbols-outlined text-[26px]">apartment</span>
                </span>
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-ink">{{ $department->name }}</h3>
                <p class="mt-0.5 truncate text-xs text-muted">{{ $department->subtitle ?: ($department->area ?: 'Unit Bisnis Mitra') }}</p>
            </div>
            <span class="hidden shrink-0 rounded-full bg-[#f6fbf8] px-3 py-1 text-xs font-semibold text-muted sm:inline-block">
                {{ $department->business_units_count }} departemen
            </span>
            <a href="{{ route('admin.units.show', $department) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Lihat Departemen
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    @empty
        <p class="px-5 py-8 text-sm text-muted">Belum ada unit bisnis. Tambahkan terlebih dahulu melalui menu Unit Bisnis.</p>
    @endforelse
</div>
@endsection