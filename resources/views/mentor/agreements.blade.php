@extends('layouts.app')
@section('title', 'Agreement')
@section('content')
<h1 class="text-2xl font-semibold">Industry Immersion Agreement</h1>
<div class="mt-6 space-y-4">
    @forelse($agreements as $agreement)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $agreement->program->participant->user->name }} · {{ $agreement->program->businessUnit->name }}</p>
                <x-badge :status="$agreement->status" />
            </div>
            <p class="mt-2 text-sm">{{ $agreement->objective }}</p>
            <p class="mt-1 text-sm text-muted">{{ $agreement->problem_statement }}</p>
            @if(in_array($agreement->status, ['submitted','revision'], true) || $agreement->status === 'submitted')
            <form method="POST" action="{{ route('mentor.agreements.review', $agreement) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_auto_auto]">
                @csrf
                <input name="revision_note" placeholder="Catatan revisi" class="rounded-lg border border-line px-3 py-2 text-sm">
                <button name="decision" value="revision" class="rounded-lg border border-line px-4 py-2 text-sm">Minta revisi</button>
                <button name="decision" value="agreed" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Setujui</button>
            </form>
            @endif
        </article>
    @empty
        <x-empty title="Belum ada agreement" />
    @endforelse
</div>
@endsection
