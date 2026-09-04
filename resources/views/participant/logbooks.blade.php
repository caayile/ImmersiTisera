@extends('layouts.app')
@section('title', 'Logbook')
@section('content')
<h1 class="text-2xl font-semibold">Daily Logbook</h1>
@unless($program)
    <x-empty class="mt-6" title="Logbook belum tersedia">Logbook dibuka setelah program ACTIVE.</x-empty>
@else
@if($program->status === 'active')
<form method="POST" enctype="multipart/form-data" class="mt-6 grid gap-4 rounded-2xl border border-line bg-white p-6 lg:grid-cols-2">
    @csrf
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Tanggal
        <input type="date" name="date" value="{{ now()->toDateString() }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Aktivitas
        <input name="activity" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">What I did
        <textarea name="what_i_did" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required></textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">What I learned
        <textarea name="what_i_learned" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required></textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">What I found
        <textarea name="what_i_found" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required></textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Value
        <textarea name="value" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm"></textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Next action
        <textarea name="next_action" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm"></textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Attachment
        <input type="file" name="attachment" class="mt-2 w-full text-sm">
    </label>
    <div class="lg:col-span-2">
        <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Kirim logbook</button>
    </div>
</form>
@endif
<div class="mt-6 space-y-4">
    @forelse($program->logbooks as $log)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm">
            <div class="flex items-center justify-between">
                <p class="font-medium">{{ $log->date->format('d M Y') }} · {{ $log->activity }}</p>
                <x-badge :status="$log->status" />
            </div>
            <p class="mt-2"><b>Did:</b> {{ $log->what_i_did }}</p>
            <p><b>Learned:</b> {{ $log->what_i_learned }}</p>
            <p><b>Found:</b> {{ $log->what_i_found }}</p>
            @if($log->mentor_feedback)<p class="mt-2 text-primary-dark"><b>Feedback mentor:</b> {{ $log->mentor_feedback }}</p>@endif
            @if($log->attachment_path)<a class="mt-2 inline-block text-primary-dark" href="{{ asset('storage/'.$log->attachment_path) }}">Lihat lampiran</a>@endif
        </article>
    @empty
        <p class="text-sm text-muted">Belum ada logbook.</p>
    @endforelse
</div>
@endif
@endsection
