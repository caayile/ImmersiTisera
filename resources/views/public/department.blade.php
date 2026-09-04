@extends('layouts.public')
@section('title', $department->name)
@section('content')
<div class="mx-auto max-w-6xl px-5 py-12">
    <p class="text-sm text-primary-dark">{{ $department->area ?: 'Departemen' }}</p>
    <h1 class="mt-1 text-3xl font-semibold">{{ $department->name }}</h1>
    <p class="mt-3 max-w-3xl text-muted">{{ $department->description }}</p>
    <p class="mt-2 text-sm">Fungsi: {{ $department->function }} · Area: {{ $department->area }}</p>

    @if($department->isDirectPlacement())
        <div class="mt-10 rounded-2xl border border-line bg-white p-6">
            <h2 class="text-xl font-semibold">Penempatan langsung</h2>
            <p class="mt-2 text-sm text-muted">Departemen ini tidak memiliki unit bisnis terpisah. Pengajuan imersi diajukan langsung ke departemen.</p>
            @auth
                <a href="{{ route('participant.applications.create', ['unit' => $department->primaryUnit()?->id]) }}" class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Ajukan ke departemen ini</a>
            @else
                <a href="{{ route('login') }}" class="mt-5 inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Masuk untuk mengajukan</a>
            @endauth
        </div>
    @else
        <h2 class="mt-10 text-xl font-semibold">Unit Bisnis</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @forelse($department->businessUnits as $unit)
                <article class="rounded-xl border border-line bg-white p-5">
                    <h3 class="font-semibold">{{ $unit->name }}</h3>
                    <p class="mt-2 text-sm text-muted">{{ \Illuminate\Support\Str::limit($unit->description, 110) }}</p>
                    <a href="{{ route('units.show', $unit) }}" class="mt-3 inline-block text-sm font-semibold text-primary-dark">Lihat unit</a>
                </article>
            @empty
                <p class="text-sm text-muted">Belum ada unit bisnis.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
