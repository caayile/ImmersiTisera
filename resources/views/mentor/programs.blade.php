@extends('layouts.app')
@section('title', 'Program Aktif')
@section('content')
<h1 class="text-2xl font-semibold">Program Aktif</h1>
<div class="mt-6 space-y-3">
    @forelse($programs as $program)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $program->participant->user->name }} · {{ $program->businessUnit->name }}</p>
                <x-badge :status="$program->status" />
            </div>
            <p class="mt-2 text-sm text-muted">Agreement {{ $program->agreement?->status ?? 'draft' }} · progress {{ $program->progress }}%</p>
        </article>
    @empty
        <x-empty title="Tidak ada program" />
    @endforelse
</div>
@endsection
