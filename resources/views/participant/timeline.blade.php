@extends('layouts.app')
@section('title', 'Linimasa')
@section('content')
<div>
    <h1 class="text-2xl font-semibold">Linimasa Saya</h1>
    <p class="mt-1 text-sm text-muted">Kemajuan 8 minggu program magang Anda, diperbarui mengikuti minggu berjalan.</p>
</div>
@unless($program)
    <x-empty class="mt-6" title="Linimasa belum aktif">Linimasa muncul setelah program dimulai.</x-empty>
@else
<div class="mt-6 flex flex-wrap items-center gap-3 rounded-2xl border border-line bg-white px-5 py-4 shadow-sm">
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Unit Bisnis</p>
        <p class="mt-0.5 font-medium">{{ $program->department?->name ?? '—' }} · {{ $program->businessUnit?->name ?? '—' }}</p>
    </div>
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Minggu berjalan</p>
        <p class="mt-0.5 font-medium">Minggu {{ $program->current_week ?? 1 }} / 8</p>
    </div>
    <div class="ml-auto"><x-badge :status="$program->status" /></div>
</div>
<div class="mt-6 space-y-0">
    @forelse($program->timelines->sortBy('week') as $item)
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $item->week == $program->current_week ? 'bg-primary text-white' : ($item->status === 'done' ? 'bg-primary/15 text-primary-dark' : 'border border-line bg-white text-muted') }}">{{ $item->week }}</span>
                @if(! $loop->last)
                    <span class="w-0.5 flex-1 {{ $item->status === 'done' ? 'bg-primary/40' : 'bg-line' }}"></span>
                @endif
            </div>
            <article class="mb-5 flex-1 rounded-2xl border border-line bg-white p-5 {{ $item->week == $program->current_week ? 'border-primary shadow-sm ring-1 ring-primary/30' : '' }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Minggu {{ $item->week }}</p>
                    <x-badge :status="$item->status" />
                </div>
                <h2 class="mt-1 font-semibold">{{ \App\Support\Status::timelineText($item->title) }}</h2>
                @if($item->description)
                    <p class="mt-1 text-sm text-muted">{{ \App\Support\Status::timelineText($item->description) }}</p>
                @endif
                @if($item->expected_output)
                    <p class="mt-2 text-sm"><b>Target hasil:</b> {{ \App\Support\Status::timelineText($item->expected_output) }}</p>
                @endif
                @if($item->mentor_note)
                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800"><b>Catatan mentor:</b> {{ $item->mentor_note }}</p>
                @endif
                @if($item->status === 'submitted')
                    <p class="mt-2 text-xs font-semibold text-primary-dark">Diajukan — menunggu pengesahan mentor.</p>
                @endif
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm font-semibold text-primary-dark">Ubah rencana minggu ini</summary>
                    <form method="POST" action="{{ route('participant.timeline.update', $item) }}" class="mt-3 space-y-3 rounded-xl border border-line bg-bg/60 p-4">
                        @csrf
                        <div>
                            <label class="text-xs font-medium text-muted">Judul</label>
                            <input name="title" value="{{ $item->title }}" maxlength="180" class="mt-1 w-full rounded-lg border border-line bg-white px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted">Deskripsi</label>
                            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-line bg-white px-3 py-2 text-sm">{{ $item->description }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted">Target hasil</label>
                            <textarea name="expected_output" rows="2" class="mt-1 w-full rounded-lg border border-line bg-white px-3 py-2 text-sm">{{ $item->expected_output }}</textarea>
                        </div>
                        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan & ajukan</button>
                    </form>
                </details>
            </article>
        </div>
    @empty
        <x-empty class="mt-6" title="Belum ada tonggak" />
    @endforelse
</div>
@endunless
@endsection
