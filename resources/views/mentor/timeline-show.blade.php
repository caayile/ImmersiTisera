@extends('layouts.app')
@section('title', 'Checkpoint')
@section('content')
<div>
    <a href="{{ route('mentor.timeline') }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke daftar checkpoint</a>
    <h1 class="mt-3 text-2xl font-semibold">Checkpoint {{ $program->participant->user->name }}</h1>
    <p class="mt-1 text-sm text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p>
</div>
    <section class="mt-6 rounded-2xl border border-line bg-white p-5">
        <div class="space-y-3">
            @foreach($program->timelines->whereIn('week', \App\Support\Status::CHECKPOINT_WEEKS)->sortBy('week') as $timeline)
                <details class="rounded-xl border border-line p-4 text-sm {{ $timeline->status === 'submitted' ? 'border-amber-300 bg-amber-50/40' : '' }}" @if($timeline->status === 'submitted') open @endif>
                    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2">
                        <span><b>Minggu {{ $timeline->week }}</b> · {{ \App\Support\Status::timelineText($timeline->title) }}</span>
                        <x-badge :status="$timeline->status" />
                    </summary>
                    <div class="mt-3 space-y-1 text-sm">
                        @if($timeline->description)
                            <p class="text-muted">{{ \App\Support\Status::timelineText($timeline->description) }}</p>
                        @endif
                        @if($timeline->expected_output)
                            <p><b>Target hasil:</b> {{ \App\Support\Status::timelineText($timeline->expected_output) }}</p>
                        @endif
                        @if($timeline->attachment_path)
                            <a href="{{ asset('storage/'.$timeline->attachment_path) }}" target="_blank" rel="noopener" class="mt-2 inline-block font-semibold text-primary-dark">Unduh laporan terlampir</a>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('mentor.timeline.review', $timeline) }}" class="mt-3 grid gap-2 border-t border-line pt-3 md:grid-cols-[1fr_160px_auto]">
                        @csrf
                        <input name="mentor_note" value="{{ $timeline->mentor_note }}" placeholder="Catatan (wajib jika revisi)" class="rounded-lg border border-line px-3 py-2 text-sm">
                        <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <option value="done" @selected($timeline->status === 'done')>Sahkan</option>
                            <option value="pending" @selected($timeline->status !== 'done')>Minta revisi</option>
                        </select>
                        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                    </form>
                </details>
            @endforeach
        </div>
    </section>
@endsection
