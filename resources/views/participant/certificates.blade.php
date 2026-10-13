@extends('layouts.app')
@section('title', 'Sertifikat Magang')
@section('content')
<p class="text-sm text-muted">Sertifikat penyelesaian magang muncul setelah nilai raport diumumkan mentor dan Anda menyelesaikan kuesioner (siklus berjalan).</p>

@if($pendingNotice ?? null)
    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-950">
        <p class="font-semibold">{{ $pendingNotice['title'] }}</p>
        <p class="mt-1 leading-6">{{ $pendingNotice['message'] }}</p>
        @if(filled($pendingNotice['action_url'] ?? null))
            <a href="{{ $pendingNotice['action_url'] }}" class="mt-3 inline-flex items-center gap-1 font-semibold text-primary-dark hover:underline">
                {{ $pendingNotice['action_label'] }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        @endif
    </div>
@endif

<div class="mt-6 space-y-4">
    @forelse($certificates as $certificate)
        @php
            $cycle = $certificate->program;
            $mentorReport = $cycle?->evaluations
                ->whereNotIn('evaluator_id', [auth()->id()])
                ->first(fn ($eval) => $eval->hasReport());
        @endphp
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-primary-dark">
                        <span class="material-symbols-outlined text-[16px]">workspace_premium</span>
                        Sertifikat penyelesaian
                    </p>
                    <p class="mt-2 text-lg font-semibold">{{ $certificate->number }}</p>
                    <p class="mt-1 text-sm text-muted">
                        {{ $cycle->department?->name ?? '—' }}
                        · {{ $cycle->businessUnit?->name ?? '—' }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        Periode {{ $cycle->start_date?->format('M Y') ?? '—' }} – {{ $cycle->end_date?->format('M Y') ?? '—' }}
                        · Terbit {{ $certificate->issued_at?->format('d M Y') ?? '—' }}
                    </p>
                    @if($mentorReport)
                        <p class="mt-2 text-sm">
                            Nilai raport:
                            <span class="font-semibold {{ \App\Models\Evaluation::predicateColor($mentorReport->predicate()) }}">{{ $mentorReport->predicate() ?? '—' }}</span>
                            <span class="text-muted">({{ $mentorReport->reportAverage() ?? '—' }})</span>
                        </p>
                    @endif
                </div>
                <a href="{{ route('participant.certificates.print', $certificate) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    Lihat / Cetak
                    <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                </a>
            </div>
        </article>
    @empty
        <x-empty title="Belum ada sertifikat yang bisa dibuka">
            Sertifikat akan tampil di halaman ini setelah mentor mengumumkan nilai dan (untuk magang berjalan) Anda mengisi kuesioner evaluasi.
        </x-empty>
    @endforelse
</div>
@endsection
