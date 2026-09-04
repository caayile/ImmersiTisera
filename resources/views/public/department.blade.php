@extends('layouts.public')
@section('title', $department->name)
@section('content')
<div class="mx-auto max-w-6xl px-5 py-12">
    <p class="text-sm text-primary-dark">Department</p>
    <h1 class="mt-1 text-3xl font-semibold">{{ $department->name }}</h1>
    <p class="mt-3 max-w-3xl text-muted">{{ $department->description }}</p>
    <p class="mt-2 text-sm">Function: {{ $department->function }} · Area: {{ $department->area }}</p>
    <h2 class="mt-10 text-xl font-semibold">Unit Bisnis</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-3">
        @foreach($department->businessUnits as $unit)
            <article class="rounded-xl border border-line bg-white p-5">
                <h3 class="font-semibold">{{ $unit->name }}</h3>
                <p class="mt-2 text-sm text-muted">{{ \Illuminate\Support\Str::limit($unit->description, 110) }}</p>
                <a href="{{ route('units.show', $unit) }}" class="mt-3 inline-block text-sm font-semibold text-primary-dark">Lihat unit</a>
            </article>
        @endforeach
    </div>
</div>
@endsection
