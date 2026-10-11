@extends('layouts.app')
@section('title', 'Hasil Magang')
@section('content')
@php
    $myId = auth()->id();
    $topReport = $topCycle ? $topCycle->evaluations->whereNotIn('evaluator_id', [$myId])->first(fn ($eval) => $eval->hasReport()) : null;
    $legacyEvals = $program ? $program->evaluations->whereNotIn('evaluator_id', [$myId])->reject(fn ($eval) => $eval->hasReport())->values() : collect();
    $ownFilled = (bool) $mine;
    $legacyLabels = $legacyEvals->flatMap(fn ($eval) => collect($eval->criteriaForDisplay())->pluck('label'))->unique()->values();
    $quizIndex = 0;
    $topCertificate = $topCycle ? $certificates->firstWhere('program_id', $topCycle->id) : null;
@endphp
<p class="text-sm text-muted">Lihat riwayat, nilai, dan sertifikat dari kegiatan magangmu.</p>

@unless($program)
    <x-empty class="mt-6" title="Hasil studi belum tersedia">Hasil studi muncul setelah program dimulai.</x-empty>
@else
<div x-data="{ flow: 'table', quizStep: 1 }">
    @if($topCycle)
        {{-- Kartu Magang Saat Ini --}}
        <div x-show="flow === 'table'" class="mt-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <p class="flex items-center gap-2 font-semibold">
                    <span class="material-symbols-outlined text-[20px]">work</span>
                    Magang saat ini
                </p>
                @if(! $topReport)
                    <span class="shrink-0 rounded-full bg-orange-100 px-3 py-1 text-[11px] font-semibold text-orange-700">Nilai belum diberikan oleh mentor.</span>
                @elseif(! $ownFilled)
                    <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Mentor sudah memberikan nilai dan sertifikat, segera isi kuesioner untuk melihat</span>
                @endif
            </div>
            <p class="mt-3 text-lg font-bold">{{ $topCycle->department?->name ?? '—' }} - Departemen {{ $topCycle->businessUnit?->name ?? '—' }}</p>
            <p class="mt-0.5 text-sm text-muted">{{ $topCycle->start_date?->format('F Y') ?? '—' }} – {{ $topCycle->end_date?->format('F Y') ?? '—' }}</p>

            @if(! $topReport)
                @if($legacyEvals->isEmpty())
                    <p class="mt-3 border-t border-line pt-3 text-sm text-muted">Nilai raport dari mentor terkunci. Pengumuman nilai periode ini ditutup.</p>
                @endif
                @if($topCertificate)
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4 text-sm">
                        <div>
                            <p class="font-semibold">Sertifikat</p>
                            <p class="mt-1 font-medium">{{ $topCertificate->number }}</p>
                            <p class="mt-0.5 text-muted">{{ $topCycle->businessUnit?->name ?? '—' }} · Terbit {{ $topCertificate->issued_at?->format('d M Y') ?? '—' }}</p>
                        </div>
                        <a href="{{ route('participant.certificates.print', $topCertificate) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Sertifikat <span aria-hidden="true">↗</span></a>
                    </div>
                @endif
            @elseif(! $ownFilled)
                <div class="mt-4 flex justify-end border-t border-line pt-4">
                    <button type="button" @click="flow = 'quiz'; quizStep = 1" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Isi Kuesioner Mentor</button>
                </div>
                <p class="mt-3 flex items-center gap-1.5 border-t border-line bg-bg/60 px-5 py-3 text-sm text-muted">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    Nilai raport terkunci sampai kuesioner diisi.
                </p>
            @else
                <div class="mt-4 space-y-4 border-t border-line pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-2xl font-bold {{ \App\Models\Evaluation::predicateColor($topReport->predicate()) }}">{{ $topReport->predicate() ?? '—' }}</p>
                        <a href="{{ route('participant.evaluation.report') }}" class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Detail <span aria-hidden="true">↗</span></a>
                    </div>
                    <div class="flex flex-wrap items-start justify-between gap-3 text-sm">
                        <div>
                            <p class="font-semibold">Sertifikat</p>
                            @if($topCertificate)
                                <p class="mt-1 font-medium">{{ $topCertificate->number }}</p>
                                <p class="mt-0.5 text-muted">{{ $topCycle->businessUnit?->name ?? '—' }} · Terbit {{ $topCertificate->issued_at?->format('d M Y') ?? '—' }}</p>
                            @else
                                <p class="mt-1 font-medium text-[#22b07d]">Tersedia jika sudah diterbitkan</p>
                            @endif
                        </div>
                        @if($topCertificate)
                            <a href="{{ route('participant.certificates.print', $topCertificate) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Sertifikat <span aria-hidden="true">↗</span></a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Kuesioner 3 langkah --}}
        @if($topReport && ! $ownFilled)
            <div x-show="flow !== 'table'" x-cloak>
                <button type="button" @click="flow = 'table'" class="mt-6 text-sm font-medium text-primary-dark">&larr; Kembali ke hasil magang</button>
                <form method="POST" action="{{ route('participant.evaluation') }}" class="mt-3">
                    @csrf
                    @if($errors->any())
                        <p class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-sm text-red-700">{{ $errors->first() }}</p>
                    @endif
                    @foreach($bank as $sectionIndex => $section)
                        <section x-show="quizStep === {{ $sectionIndex + 1 }}" class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                            <p class="border-b border-line px-5 py-3 text-sm font-semibold">{{ $section['title'] }}</p>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[720px] text-sm">
                                    <thead>
                                        <tr class="border-b border-line text-muted">
                                            <th class="w-10 px-4 py-3 text-left font-medium">No</th>
                                            <th class="px-4 py-3 text-left font-medium">Pertanyaan</th>
                                            @foreach($scaleList as $scale)
                                                <th class="w-24 px-2 py-3 text-center font-medium">{{ $scale['label'] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($section['questions'] as $question)
                                            <tr class="border-b border-line last:border-0">
                                                <td class="px-4 py-3 text-muted">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-3">{{ $question }}</td>
                                                <input type="hidden" name="criteria[{{ $quizIndex }}][label]" value="{{ $question }}">
                                                @foreach($scaleList as $scale)
                                                    <td class="px-2 py-3 text-center">
                                                        <input type="radio" name="criteria[{{ $quizIndex }}][score]" value="{{ $scale['value'] }}" class="h-5 w-5 accent-[#22b07d]" required>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @php $quizIndex++; @endphp
                                        @endforeach
                                        @if($loop->last)
                                            <tr>
                                                <td class="px-4 py-3 text-muted">{{ count($section['questions']) + 1 }}</td>
                                                <td class="px-4 py-3 font-medium">Kritik Dan Saran</td>
                                                <td colspan="{{ count($scaleList) }}" class="px-4 py-3">
                                                    <textarea name="comments" rows="3" class="w-full rounded-xl border border-line px-4 py-2.5 text-sm">{{ old('comments', $mine?->comments) }}</textarea>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex items-center justify-between gap-2 px-5 py-4">
                                <p class="text-xs text-muted">Langkah {{ $sectionIndex + 1 }} dari {{ count($bank) }}</p>
                                <div class="flex gap-2">
                                    @if($sectionIndex > 0)
                                        <button type="button" @click="quizStep--" class="rounded-xl border border-primary/40 px-5 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">&lsaquo; Sebelumnya</button>
                                    @endif
                                    @if($sectionIndex < count($bank) - 1)
                                        <button type="button" @click="quizStep++" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Selanjutnya &rsaquo;</button>
                                    @else
                                        <button class="rounded-xl bg-[#22b07d] px-8 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Kirim</button>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endforeach
                </form>
            </div>
        @endif
    @endif

    {{-- Riwayat hasil magang --}}
    @if($history->isNotEmpty())
        <h2 id="riwayat" class="mt-8 scroll-mt-24 text-lg font-semibold">Riwayat Hasil Magang Dosen</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-sm">
                    <thead>
                        <tr class="border-b border-line text-muted">
                            <th class="w-10 px-5 py-3 text-left font-medium">No</th>
                            <th class="px-5 py-3 text-left font-medium">Periode</th>
                            <th class="px-5 py-3 text-left font-medium">Unit Bisnis</th>
                            <th class="px-5 py-3 text-left font-medium">Departemen</th>
                            <th class="px-5 py-3 text-left font-medium">Nilai</th>
                            <th class="px-5 py-3 text-left font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $cycle)
                            @php
                                $cycleReport = $cycle->evaluations->whereNotIn('evaluator_id', [auth()->id()])->first(fn ($eval) => $eval->hasReport());
                                $cycleCertificate = $certificates->firstWhere('program_id', $cycle->id);
                            @endphp
                            @if($cycleReport || $cycleCertificate)
                                <tr class="border-b border-line">
                                    <td class="px-5 py-3 align-top text-muted" rowspan="2">{{ $loop->iteration }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 align-top font-medium">Periode {{ $cycle->start_date?->format('M Y') ?? '—' }} – {{ $cycle->end_date?->format('M Y') ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">{{ $cycle->department?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">{{ $cycle->businessUnit?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">
                                        @if($cycleReport)
                                            <span class="font-semibold {{ \App\Models\Evaluation::predicateColor($cycleReport->predicate()) }}">{{ $cycleReport->predicate() ?? '—' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 align-top">
                                        @if($cycleReport)
                                            <a href="{{ route('participant.evaluation.report', $cycle) }}" class="font-semibold text-[#22b07d] hover:underline">Lihat Detail Nilai</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-b border-line last:border-0">
                                    <td class="whitespace-nowrap px-5 py-3 align-top font-medium">Sertifikat</td>
                                    <td class="px-5 py-3 align-top" colspan="2">
                                        @if($cycleCertificate)
                                            <span class="font-medium">{{ $cycleCertificate->number }}</span>
                                            <span class="text-muted">{{ $cycle->businessUnit?->name ?? '' }} · Terbit {{ $cycleCertificate->issued_at?->format('d M Y') ?? '—' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 align-top"><span class="text-muted">—</span></td>
                                    <td class="px-5 py-3 align-top">
                                        @if($cycleCertificate)
                                            <a href="{{ route('participant.certificates.print', $cycleCertificate) }}" target="_blank" rel="noopener" class="font-semibold text-[#22b07d] hover:underline">Lihat Sertifikat</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($legacyEvals->isNotEmpty())
        <div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white shadow-sm">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-b border-line text-muted">
                        <th class="px-5 py-3 font-medium">No</th>
                        <th class="px-5 py-3 font-medium">Penilai</th>
                        @foreach($legacyLabels as $label)
                            <th class="px-5 py-3 font-medium">{{ $label }}</th>
                        @endforeach
                        <th class="px-5 py-3 font-medium">Rata-rata</th>
                        <th class="px-5 py-3 font-medium">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($legacyEvals as $index => $legacy)
                        @php $legacyScores = collect($legacy->criteriaForDisplay())->keyBy('label'); @endphp
                        <tr class="border-b border-line last:border-0">
                            <td class="px-5 py-3 align-top">{{ $index + 1 }}</td>
                            <td class="px-5 py-3 align-top font-medium">{{ $legacy->evaluator->name }}</td>
                            @foreach($legacyLabels as $label)
                                <td class="px-5 py-3 align-top">{{ $legacyScores->has($label) ? $legacyScores[$label]['score'].' / 5' : '—' }}</td>
                            @endforeach
                            <td class="px-5 py-3 align-top font-semibold">{{ $legacy->average() }} / 5</td>
                            <td class="px-5 py-3 align-top text-muted">{{ $legacy->comments ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endunless
@endsection
