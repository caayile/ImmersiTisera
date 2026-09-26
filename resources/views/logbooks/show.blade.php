@extends('layouts.app')
@section('title', 'Detail Logbook')
@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <a href="{{ route($backRoute) }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke daftar logbook</a>
        <h1 class="mt-3 text-2xl font-semibold">Logbook {{ $program->participant->user->name }}</h1>
        <p class="mt-1 text-sm text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p>
    </div>
    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary-dark">{{ $program->logbooks->count() }} entri</span>
</div>

<div class="mt-6 space-y-4">
    @forelse($program->logbooks as $log)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm {{ $log->status === 'submitted' ? 'ring-1 ring-amber-300' : '' }}">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div><p class="font-semibold">{{ $log->activity }}</p><p class="mt-1 text-xs text-muted">{{ $log->date->format('d M Y') }}</p></div>
                <x-badge :status="$log->status" />
            </div>
            <dl class="mt-4 space-y-2"><div><dt class="font-semibold">Did</dt><dd>{{ $log->what_i_did }}</dd></div><div><dt class="font-semibold">Learned</dt><dd>{{ $log->what_i_learned }}</dd></div><div><dt class="font-semibold">Found</dt><dd>{{ $log->what_i_found }}</dd></div></dl>
            @if($canReview)
                <form method="POST" action="{{ route('mentor.logbooks.review', $log) }}" class="mt-4 grid gap-2 border-t border-line pt-4 md:grid-cols-[1fr_180px_auto]">
                    @csrf
                    <input name="mentor_feedback" value="{{ $log->mentor_feedback }}" placeholder="Feedback / next action" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm"><option value="reviewed">Reviewed</option><option value="revision">Revision required</option><option value="approved">Approved</option></select>
                    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                </form>
            @elseif($log->mentor_feedback)
                <div class="mt-4 border-t border-line pt-4"><p class="font-semibold">Feedback mentor</p><p class="mt-1">{{ $log->mentor_feedback }}</p></div>
            @endif
        </article>
    @empty
        <x-empty title="Belum ada logbook" />
    @endforelse
</div>
@endsection