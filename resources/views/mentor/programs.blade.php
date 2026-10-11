@extends('layouts.app')
@section('title', 'Program Aktif')
@section('content')
<div class="space-y-3">
    @forelse($programs as $program)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $program->participant->user->name }} · {{ $program->businessUnit->name }}</p>
                <x-badge :status="$program->status" />
            </div>
            <p class="mt-2 text-sm text-muted">Perjanjian {{ \App\Support\Status::label($program->agreement?->status ?? 'draft') }} · kemajuan {{ $program->progress }}%</p>
        </article>
    @empty
        <x-empty title="Tidak ada program" />
    @endforelse
</div>
@endsection
