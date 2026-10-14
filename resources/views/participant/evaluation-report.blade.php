@extends('layouts.app')
@section('title', 'Detail Raport')
@section('content')
@php $reportNo = 0; $eval = $report; @endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('participant.evaluation') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-4 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
        <span aria-hidden="true">&larr;</span>
        Kembali
    </a>
    <a href="{{ route('participant.evaluation.report-preview', $program) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
        <span class="material-symbols-outlined text-[18px]">print</span>
        Cetak PDF
    </a>
</div>

<article class="mt-4 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
    <div class="border-b-2 border-primary-dark/70 px-5 pb-4 pt-5 text-center sm:px-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Nilai raport dari {{ $eval->evaluator->name }}</p>
        <h2 class="mt-1 text-xl font-bold tracking-wide">LAPORAN HASIL MAGANG</h2>
        <p class="mt-0.5 text-sm text-muted">Program Magang Dosen TSU</p>
    </div>
    <dl class="grid gap-x-8 gap-y-2 px-5 py-4 text-sm sm:grid-cols-2 sm:px-8">
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Nama</dt><dd class="font-medium">: {{ $program->participant?->user?->name ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">NIDN</dt><dd class="font-medium">: {{ $program->participant?->nidn ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Program Studi</dt><dd class="font-medium">: {{ $program->participant?->study_program ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Fakultas</dt><dd class="font-medium">: {{ $program->participant?->faculty ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Unit Bisnis</dt><dd class="font-medium">: {{ $program->department?->name ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Departemen</dt><dd class="font-medium">: {{ $program->businessUnit?->name ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Periode</dt><dd class="font-medium">: {{ $program->start_date?->format('M Y') ?? '—' }} – {{ $program->end_date?->format('M Y') ?? '—' }}</dd></div>
        <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted">Mentor</dt><dd class="font-medium">: {{ $eval->evaluator->name }}</dd></div>
    </dl>
    <div class="overflow-x-auto px-5 pb-2 sm:px-8">
        <table class="w-full min-w-[560px] border-collapse text-left text-sm">
            <thead>
                <tr class="bg-[#eef6f2] font-semibold">
                    <th class="w-12 border border-line px-4 py-2.5 text-center">No</th>
                    <th class="border border-line px-4 py-2.5">Komponen Penilaian</th>
                    <th class="w-24 border border-line px-4 py-2.5 text-center">Angka</th>
                    <th class="w-24 border border-line px-4 py-2.5 text-center">Huruf</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eval->grade_groups as $group)
                    <tr>
                        <td colspan="4" class="border border-line bg-[#f4f8f6] px-4 py-2.5 font-semibold">{{ $group['name'] }} <span class="font-normal text-muted">(bobot {{ $group['weight'] }}%)</span></td>
                    </tr>
                    @foreach($group['aspects'] ?? [] as $aspect)
                        @php $reportNo++; @endphp
                        <tr>
                            <td class="border border-line px-4 py-2.5 text-center text-muted">{{ $reportNo }}</td>
                            <td class="border border-line px-4 py-2.5">{{ $aspect['label'] }}</td>
                            <td class="border border-line px-4 py-2.5 text-center font-semibold">{{ $aspect['score'] }}</td>
                            <td class="border border-line px-4 py-2.5 text-center font-semibold">{{ \App\Models\Evaluation::predicateFor(isset($aspect['score']) ? (float) $aspect['score'] : null) ?? '—' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[#eef6f2] font-bold">
                    <td colspan="2" class="border border-line px-4 py-3">Nilai Akhir</td>
                    <td class="border border-line px-4 py-3 text-center">{{ $eval->reportAverage() }}</td>
                    <td class="border border-line px-4 py-3 text-center text-primary-dark">{{ $eval->predicate() }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="border border-line px-4 py-2.5 text-muted">Predikat Kelulusan</td>
                    <td colspan="2" class="border border-line px-4 py-2.5 font-semibold">{{ $eval->predicate() }} · {{ \App\Models\Evaluation::predicateLabel($eval->predicate()) ?? '—' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-4 sm:px-8">
        <p class="max-w-xl text-sm text-muted">@if(filled($eval->comments))<b class="text-ink">Catatan mentor:</b> {{ $eval->comments }}@else Belum ada catatan mentor. @endif</p>
    </div>
</article>
@endsection
