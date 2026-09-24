@extends('layouts.app')
@section('title', 'Riwayat Logbook')
@section('content')
<x-back-link />
<div class="mt-4">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Arsip imersi</p>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Riwayat Logbook</h1>
            <p class="mt-1 text-sm text-muted">Daftar periode magang per tahun, termasuk penempatan unit bisnis tiap batch.</p>
        </div>
        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">{{ $totalEntries }} entri</span>
    </div>
</div>

@forelse($years as $year => $items)
    <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Batch {{ $year }}</p>
        <div class="mt-3 space-y-4">
            @foreach($items as $program)
                <article class="rounded-2xl border border-line bg-white p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">
                                Batch {{ $program->start_date?->year ?? ($program->end_date?->year ?? '—') }}
                            </p>
                            <h2 class="mt-1 font-semibold">{{ $program->businessUnit?->name ?? 'Program imersi' }}</h2>
                            <p class="mt-0.5 text-sm text-muted">{{ $program->department?->name ?? 'Unit bisnis belum ditentukan' }}</p>
                        </div>
                        <x-badge :status="$program->status" />
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Periode</p>
                            <p class="mt-0.5 font-medium">
                                {{ $program->start_date?->format('d M Y') ?? '—' }} – {{ $program->end_date?->format('d M Y') ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Mentor</p>
                            <p class="mt-0.5 font-medium">{{ $program->mentor?->user?->name ?? 'Belum ditugaskan' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Entri</p>
                            <p class="mt-0.5 font-medium">{{ $program->logbooks->count() }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 border-t border-line pt-4">
                        @forelse($program->logbooks as $log)
                            <div class="rounded-xl border border-line bg-bg p-4 text-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-semibold">{{ $log->date->format('d M Y') }}</p>
                                    <div class="flex items-center gap-2">
                                        @if($log->status === 'approved')
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Terverifikasi</span>
                                        @endif
                                        <x-badge :status="$log->status" />
                                    </div>
                                </div>
                                <p class="mt-2"><b>Did:</b> {{ $log->what_i_did }}</p>
                                <p><b>Learned:</b> {{ $log->what_i_learned }}</p>
                                <p><b>Found:</b> {{ $log->what_i_found }}</p>
                                @if($log->mentor_feedback)<p class="mt-2 text-primary-dark"><b>Feedback mentor:</b> {{ $log->mentor_feedback }}</p>@endif
                            </div>
                        @empty
                            <p class="text-sm text-muted">Belum ada entri logbook pada periode ini.</p>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@empty
    <div class="mt-6 rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
        <p class="text-sm text-muted">Belum ada riwayat logbook.</p>
    </div>
@endforelse
@endsection