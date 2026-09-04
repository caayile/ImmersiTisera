@extends('layouts.public')
@section('title', 'Departemen')
@section('content')
<div class="mx-auto max-w-6xl px-5 py-12">
    <h1 class="text-3xl font-semibold">Departemen</h1>
    <p class="mt-2 text-muted">Pilih department, lalu unit bisnis, lalu mentor — linear matching.</p>
    @if($q)
        <p class="mt-3 text-sm text-primary-dark">Hasil pencarian: “{{ $q }}”</p>
    @endif
    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @forelse($departments as $department)
            <article class="rounded-2xl border border-line bg-white p-6 hover:border-primary">
                <h2 class="text-xl font-semibold">{{ $department->name }}</h2>
                <p class="mt-2 text-sm text-muted">{{ $department->description }}</p>
                <p class="mt-3 text-sm">Area: {{ $department->function }}</p>
                <p class="mt-1 text-sm text-muted">{{ $department->business_units_count }} unit bisnis</p>
                <a href="{{ route('departments.show', $department) }}" class="mt-4 inline-block rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lihat Detail</a>
            </article>
        @empty
            <p class="text-muted">Tidak ada department yang cocok.</p>
        @endforelse
    </div>
</div>
@endsection
