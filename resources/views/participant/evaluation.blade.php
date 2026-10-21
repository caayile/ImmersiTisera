@extends('layouts.app')
@section('title', 'Hasil Nilai Magang')
@section('content')
@php
    $myId = auth()->id();
    $topReport = $topCycle ? $topCycle->evaluations->whereNotIn('evaluator_id', [$myId])->first(fn ($eval) => $eval->hasReport()) : null;
    $legacyEvals = $program ? $program->evaluations->whereNotIn('evaluator_id', [$myId])->reject(fn ($eval) => $eval->hasReport())->values() : collect();
    $ownFilled = (bool) $mine;
    $legacyLabels = $legacyEvals->flatMap(fn ($eval) => collect($eval->criteriaForDisplay())->pluck('label'))->unique()->values();
    $quizIndex = 0;
    $topCertificate = $topCycle ? $certificates->firstWhere('program_id', $topCycle->id) : null;
    $startOnQuiz = $topReport && ! $ownFilled && $errors->any();
@endphp
<p class="text-sm text-muted">Alur: isi kuesioner, lalu nilai raport dan sertifikat terbuka di halaman ini.</p>

@unless($program)
    <x-empty class="mt-6" title="Hasil studi belum tersedia">Hasil studi muncul setelah program dimulai.</x-empty>
@else
<script>
    window.evaluationQuiz = function (startOnQuiz) {
        return {
            flow: startOnQuiz ? 'quiz' : 'table',
            quizStep: 1,
            quizError: '',
            stepSection(step) {
                return this.$root.querySelector('[data-quiz-step="' + step + '"]');
            },
            stepIsComplete(step) {
                const section = this.stepSection(step);
                if (! section) {
                    return true;
                }
                const names = [...new Set([...section.querySelectorAll('input[type=radio]')].map((input) => input.name))];

                return names.every((name) => section.querySelector('input[name="' + CSS.escape(name) + '"]:checked'));
            },
            validateCurrentStep() {
                this.quizError = '';
                if (this.stepIsComplete(this.quizStep)) {
                    return true;
                }
                this.quizError = 'Lengkapi semua jawaban di langkah ini sebelum lanjut.';

                return false;
            },
            goNext() {
                if (this.validateCurrentStep()) {
                    this.quizStep++;
                }
            },
            submitQuiz(event) {
                const total = this.$root.querySelectorAll('[data-quiz-step]').length;
                for (let step = 1; step <= total; step++) {
                    if (! this.stepIsComplete(step)) {
                        event.preventDefault();
                        this.quizStep = step;
                        this.quizError = 'Lengkapi semua jawaban di langkah ' + step + ' sebelum mengirim.';

                        return;
                    }
                }
                this.quizError = '';
            },
        };
    };
</script>
<div x-data="evaluationQuiz(@js($startOnQuiz))">
    @if($topCycle)
        {{-- Kartu nilai siklus terbaru --}}
        <div x-show="flow === 'table'" class="mt-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <p class="flex items-center gap-2 font-semibold">
                    <span class="material-symbols-outlined text-[20px]">grade</span>
                    {{ $topCycle->status === 'completed' ? 'Magang terakhir' : 'Magang saat ini' }}
                </p>
                @if(! $topReport)
                    <span class="shrink-0 rounded-full bg-orange-100 px-3 py-1 text-[11px] font-semibold text-orange-700">Nilai belum diberikan oleh mentor.</span>
                @elseif(! $ownFilled)
                    <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Mentor sudah memberikan nilai, segera isi kuesioner</span>
                @endif
            </div>
            <p class="mt-3 text-lg font-bold">{{ $topCycle->department?->name ?? '—' }} - Departemen {{ $topCycle->businessUnit?->name ?? '—' }}</p>
            <p class="mt-0.5 text-sm text-muted">{{ $topCycle->start_date?->format('F Y') ?? '—' }} – {{ $topCycle->end_date?->format('F Y') ?? '—' }}</p>

            @if(! $topReport)
                <div class="mt-4 rounded-xl border border-dashed border-line bg-bg/60 px-4 py-4 text-sm text-muted">
                    <p class="font-medium text-ink">Nilai magang belum diumumkan</p>
                    <p class="mt-1">Mentor akan mengisi dan mengesahkan raport di menu Penilaian. Setelah itu, isi kuesioner singkat di halaman ini untuk melihat nilai dan sertifikat.</p>
                </div>
            @elseif(! $ownFilled)
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-4 text-sm">
                    <p class="font-medium text-emerald-900">Nilai magang sudah diumumkan mentor</p>
                    <p class="mt-1 text-emerald-800/90">Isi kuesioner singkat terlebih dahulu. Setelah itu nilai raport dan sertifikat terbuka di halaman yang sama.</p>
                </div>
                <div class="mt-4 flex justify-end border-t border-line pt-4">
                    <button type="button" @click="flow = 'quiz'; quizStep = 1; quizError = ''" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Isi Kuesioner & Lihat Nilai</button>
                </div>
                <p class="mt-3 flex items-center gap-1.5 border-t border-line bg-bg/60 px-5 py-3 text-sm text-muted">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    Nilai raport dan sertifikat terkunci sampai kuesioner diisi.
                </p>
            @else
                <div id="hasil" class="mt-4 space-y-4 border-t border-line pt-4 scroll-mt-24">
                    <div class="rounded-xl border border-line bg-bg/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">1. Hasil nilai magang</p>
                        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="text-3xl font-bold {{ \App\Models\Evaluation::predicateColor($topReport->predicate()) }}">{{ $topReport->predicate() ?? '—' }}</p>
                                <p class="mt-1 text-sm text-muted">Rata-rata raport: <span class="font-semibold text-ink">{{ $topReport->reportAverage() ?? '—' }}</span></p>
                            </div>
                            <a href="{{ route('participant.evaluation.report', $topCycle) }}" class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Detail Nilai <span aria-hidden="true">↗</span></a>
                        </div>
                    </div>

                    <div id="sertifikat" class="rounded-xl border border-line bg-white p-4 scroll-mt-24">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">2. Sertifikat penyelesaian</p>
                        @if($topCertificate)
                            <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-dark">
                                        <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
                                        Sertifikat tersedia
                                    </p>
                                    <p class="mt-1 text-lg font-semibold">{{ $topCertificate->number }}</p>
                                    <p class="mt-1 text-xs text-muted">Terbit {{ $topCertificate->issued_at?->format('d M Y') ?? '—' }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('participant.certificates.print', $topCertificate) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                                        Lihat / Cetak
                                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                    </a>
                                    <a href="{{ route('participant.certificates.download', $topCertificate) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-primary px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-bg">
                                        Unduh PDF
                                        <span class="material-symbols-outlined text-[18px]">download</span>
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-muted">Nilai sudah terbuka. Sertifikat muncul di sini setelah mentor mengesahkan penerbitan.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Kuesioner multi-langkah --}}
        @if($topReport && ! $ownFilled)
            <div x-show="flow !== 'table'" x-cloak>
                <button type="button" @click="flow = 'table'; quizError = ''" class="mt-6 text-sm font-medium text-primary-dark">&larr; Kembali ke hasil magang</button>
                <form method="POST" action="{{ route('participant.evaluation') }}" class="mt-3" novalidate @submit="submitQuiz($event)">
                    @csrf
                    @if($errors->any())
                        <p class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-sm text-red-700">{{ $errors->first() }}</p>
                    @endif
                    <p x-show="quizError" x-cloak class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-sm text-red-700" x-text="quizError"></p>
                    @foreach($bank as $sectionIndex => $section)
                        <section data-quiz-step="{{ $sectionIndex + 1 }}" x-show="quizStep === {{ $sectionIndex + 1 }}" class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                            <p class="border-b border-line px-5 py-3 text-sm font-semibold">{{ $section['title'] }}</p>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[960px] text-sm">
                                    <thead>
                                        <tr class="border-b border-line text-muted">
                                            <th class="w-10 px-4 py-3 text-left font-medium">No</th>
                                            <th class="px-4 py-3 text-left font-medium">Pernyataan</th>
                                            @foreach($scaleList as $scale)
                                                <th class="min-w-28 px-2 py-3 text-center text-xs font-medium leading-snug">{{ $scale['label'] }}</th>
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
                                                        <input type="radio" name="criteria[{{ $quizIndex }}][score]" value="{{ $scale['value'] }}" class="h-5 w-5 accent-[#22b07d]">
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
                                        <button type="button" @click="quizError = ''; quizStep--" class="rounded-xl border border-primary/40 px-5 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">&lsaquo; Sebelumnya</button>
                                    @endif
                                    @if($sectionIndex < count($bank) - 1)
                                        <button type="button" @click="goNext()" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Selanjutnya &rsaquo;</button>
                                    @else
                                        <button type="submit" class="rounded-xl bg-[#22b07d] px-8 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Kirim</button>
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
                            @if($cycleReport)
                                <tr class="border-b border-line">
                                    <td class="px-5 py-3 align-top text-muted" rowspan="2">{{ $loop->iteration }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 align-top font-medium">Periode {{ $cycle->start_date?->format('M Y') ?? '—' }} – {{ $cycle->end_date?->format('M Y') ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">{{ $cycle->department?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">{{ $cycle->businessUnit?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 align-top">
                                        <span class="font-semibold {{ \App\Models\Evaluation::predicateColor($cycleReport->predicate()) }}">{{ $cycleReport->predicate() ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3 align-top">
                                        <a href="{{ route('participant.evaluation.report', $cycle) }}" class="font-semibold text-[#22b07d] hover:underline">Lihat Detail Nilai</a>
                                    </td>
                                </tr>
                                <tr class="border-b border-line last:border-0">
                                    <td class="whitespace-nowrap px-5 py-3 align-top font-medium">Sertifikat</td>
                                    <td class="px-5 py-3 align-top" colspan="2">
                                        @if($cycleCertificate)
                                            <span class="font-medium">{{ $cycleCertificate->number }}</span>
                                            <span class="text-muted"> · Terbit {{ $cycleCertificate->issued_at?->format('d M Y') ?? '—' }}</span>
                                        @else
                                            <span class="text-muted">Belum diterbitkan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 align-top"><span class="text-muted">—</span></td>
                                    <td class="px-5 py-3 align-top">
                                        @if($cycleCertificate)
                                            <a href="{{ route('participant.certificates.print', $cycleCertificate) }}" target="_blank" rel="noopener" class="font-semibold text-[#22b07d] hover:underline">Cetak Sertifikat</a>
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
