@extends('layouts.app')
@section('title', 'Checkpoint ' . $program->participant->user->name)
@section('content')
<div>
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-lg font-semibold">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p>
    <a href="{{ route('mentor.timeline') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-4 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
        <span aria-hidden="true">&larr;</span>
        Kembali ke daftar checkpoint
    </a>
</div>
</div>
    <section class="mt-6 rounded-2xl border border-line bg-white p-5">
        <div class="space-y-3">
            @foreach($program->timelines->whereIn('week', \App\Support\Status::CHECKPOINT_WEEKS)->sortBy('week') as $timeline)
                @php
                    $week = (int) $timeline->week;
                    $windowLabel = \App\Support\Status::checkpointWindowLabel($week);
                    $meta = \App\Support\Status::checkpointMeta($week);
                    $phaseName = $meta['title'] ?? \App\Support\Status::timelineText($timeline->title);
                    $hasUserContent = ! \App\Support\Status::isLegacyCheckpointLabel($timeline->title) || filled($timeline->description) || filled($timeline->evidence_link) || filled($timeline->attachment_path);
                    $formTitle = \App\Support\Status::isLegacyCheckpointLabel($timeline->title) ? '' : $timeline->title;
                    $formDescription = \App\Support\Status::isLegacyCheckpointLabel($timeline->description) ? '' : $timeline->description;
                    $isDone = in_array($timeline->status, ['done', 'approved'], true);
                    $isSubmitted = $timeline->status === 'submitted';
                @endphp
                <article class="rounded-xl border border-line p-4 text-sm {{ $isSubmitted ? 'border-amber-300 bg-amber-50/40' : '' }}" x-data="{ detailOpen: false }">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">{{ $windowLabel }} · {{ $phaseName }}</p>
                        <x-badge :status="$timeline->status" />
                    </div>
                    @if($formTitle)
                        <p class="mt-1 font-medium">{{ $formTitle }}</p>
                    @else
                        <p class="mt-1 text-muted">Belum ada isian dari peserta.</p>
                    @endif
                    <button type="button" @click="detailOpen = true" class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-primary/40 px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/5">
                        <span class="material-symbols-outlined text-[15px]">info</span>
                        Lihat Detail
                    </button>

                    {{-- Popup detail ala peserta + validasi --}}
                    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="detailOpen && 'flex'" @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" role="dialog" aria-modal="true" aria-label="Detail checkpoint minggu {{ $week }}">
                        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 text-left shadow-xl sm:p-8">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold">Checkpoint</h2>
                                    <p class="mt-0.5 text-sm text-muted">Minggu {{ $week }} · {{ $phaseName }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge :status="$timeline->status" />
                                    <button type="button" @click="detailOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
                                </div>
                            </div>

                            <dl class="mt-5 space-y-4 text-sm">
                                <div>
                                    <dt class="font-semibold">Judul</dt>
                                    <dd class="mt-1 rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $formTitle ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold">Deskripsi Aktivitas</dt>
                                    <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $formDescription ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold">Bukti Pendukung</dt>
                                    <dd class="mt-1 space-y-1.5">
                                        @if($timeline->evidence_link)
                                            <a href="{{ $timeline->evidence_link }}" target="_blank" rel="noopener" class="block truncate font-medium text-primary-dark">{{ $timeline->evidence_link }}</a>
                                        @endif
                                        @if($timeline->attachment_path)
                                            <a href="{{ asset('storage/'.$timeline->attachment_path) }}" target="_blank" rel="noopener" class="inline-block font-semibold text-primary-dark">Unduh laporan terlampir</a>
                                        @endif
                                        @unless($timeline->evidence_link || $timeline->attachment_path)
                                            <span class="text-muted">—</span>
                                        @endunless
                                    </dd>
                                </div>
                            </dl>

                            @if($isSubmitted)
                                <form method="POST" action="{{ route('mentor.timeline.review', $timeline) }}" class="mt-5 border-t border-line pt-4">
                                    @csrf
                                    <label class="text-sm font-semibold">Catatan mentor (opsional)</label>
                                    <input name="mentor_note" value="{{ $timeline->mentor_note }}" placeholder="Tulis catatan untuk peserta..." class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm">
                                    <div class="mt-3 flex justify-end">
                                        <button class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Validasi</button>
                                    </div>
                                </form>
                            @elseif($isDone)
                                <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">Checkpoint sudah divalidasi.</p>
                            @else
                                <p class="mt-5 rounded-xl bg-bg px-4 py-2.5 text-sm text-muted">Menunggu isian peserta.</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
