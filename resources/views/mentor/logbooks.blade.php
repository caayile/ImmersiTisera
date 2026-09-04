@extends('layouts.app')
@section('title', 'Logbook')
@section('content')
<h1 class="text-2xl font-semibold">Review Logbook</h1>
<div class="mt-6 space-y-4">
    @forelse($logbooks as $log)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm {{ $log->status === 'submitted' ? 'ring-1 ring-amber-300' : '' }}">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $log->program->participant->user->name }} · {{ $log->date->format('d M Y') }}</p>
                <div class="flex items-center gap-2">
                    @if($log->status === 'submitted')<span class="text-xs font-semibold text-amber-700">Pending Review</span>@endif
                    <x-badge :status="$log->status" />
                </div>
            </div>
            <p class="mt-2"><b>{{ $log->activity }}</b></p>
            <p>Did: {{ $log->what_i_did }}</p>
            <p>Learned: {{ $log->what_i_learned }}</p>
            <p>Found: {{ $log->what_i_found }}</p>
            <form method="POST" action="{{ route('mentor.logbooks.review', $log) }}" class="mt-3 grid gap-2 md:grid-cols-[1fr_160px_auto]">
                @csrf
                <input name="mentor_feedback" value="{{ $log->mentor_feedback }}" placeholder="Feedback / next action" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="reviewed">Reviewed</option>
                    <option value="revision">Revision required</option>
                    <option value="approved">Approved</option>
                </select>
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>
        </article>
    @empty
        <x-empty title="Tidak ada logbook" />
    @endforelse
</div>
@endsection
