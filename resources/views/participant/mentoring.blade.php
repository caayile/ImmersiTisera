@extends('layouts.app')
@section('title', 'Mentoring')
@section('content')
<h1 class="text-2xl font-semibold">30-Minute Mentor Conversation</h1>
@unless($program)
    <x-empty class="mt-6" title="Belum ada sesi mentoring" />
@else
<p class="mt-2 text-sm text-muted">Apa yang sudah ditemukan, apa yang sedang dikerjakan, apa langkah berikutnya.</p>
<div class="mt-6 space-y-4">
    @forelse($program->mentorSessions as $session)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm">
            <p class="font-medium">Minggu {{ $session->week }} · {{ $session->session_date?->format('d M Y') }}</p>
            <p class="mt-2"><b>Findings:</b> {{ $session->findings }}</p>
            <p><b>Current work:</b> {{ $session->current_work }}</p>
            <p><b>Next action:</b> {{ $session->next_action }}</p>
            <p><b>Feedback:</b> {{ $session->feedback }}</p>
            <p class="mt-2 text-muted">Checkpoint: {{ $session->checkpoint_status ?? '—' }}</p>
        </article>
    @empty
        <p class="text-sm text-muted">Belum ada histori mentoring.</p>
    @endforelse
</div>
@endif
@endsection
