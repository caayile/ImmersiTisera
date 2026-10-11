@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
@php
    $sectionOf = [];
    foreach ($bank as $section) {
        foreach ($section['questions'] as $question) {
            $sectionOf[$question] = $section['title'];
        }
    }
@endphp
<p class="text-sm text-muted">Rekap kuesioner peserta per departemen untuk menilai unit bisnis, plus rincian isian tiap responden.</p>

<form method="GET" class="mt-4 flex flex-wrap items-center gap-3">
    <select name="department_id" onchange="this.form.submit()" class="rounded-lg border border-line bg-white px-3 py-2 text-sm">
        <option value="">Semua departemen</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
    @if(request()->filled('department_id'))
        <a href="{{ route('admin.evaluations') }}" class="text-sm font-semibold text-primary-dark hover:underline">Reset</a>
    @endif
</form>

<h2 class="mt-8 font-semibold">Rekap per departemen</h2>
<p class="mt-1 text-xs text-muted">Rata-rata skala 1–5 dari kuesioner peserta. Kolom "Aspek terendah" menunjukkan pertanyaan dengan nilai paling kecil — bahan evaluasi departemen.</p>
<div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Responden</th><th class="px-4 py-3">Rata-rata</th><th class="px-4 py-3">Rata-rata per bagian</th><th class="px-4 py-3">Aspek terendah</th></tr></thead>
        <tbody>
        @forelse($recap as $row)
            <tr class="border-t border-line">
                <td class="px-4 py-3 font-semibold">{{ $row['department']->name }}</td>
                <td class="px-4 py-3">{{ $row['respondents'] }}</td>
                <td class="px-4 py-3 text-lg font-bold">{{ $row['overall'] }} <span class="text-xs font-medium text-muted">/ 5</span></td>
                <td class="px-4 py-3">
                    <ul class="space-y-1 text-xs text-muted">
                        @foreach($row['bySection'] as $sectionTitle => $avg)
                            <li class="flex items-center justify-between gap-3"><span>{{ $sectionTitle }}</span><b class="text-ink">{{ $avg }}</b></li>
                        @endforeach
                    </ul>
                </td>
                <td class="px-4 py-3">
                    <p class="text-sm">{{ $row['lowestQuestion'] }}</p>
                    <p class="mt-0.5 inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-700">{{ $row['lowestScore'] }} / 5</p>
                </td>
            </tr>
        @empty
            <tr class="border-t border-line"><td colspan="5" class="px-4 py-8 text-center text-sm text-muted">Belum ada kuesioner peserta yang terisi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<h2 class="mt-8 font-semibold">Rincian isian kuesioner</h2>
<p class="mt-1 text-xs text-muted">Jawaban per pertanyaan dari tiap peserta, dikelompokkan per bagian kuesioner.</p>
<div class="mt-3 space-y-4">
    @forelse($questionnaires as $evaluation)
        @php
            $grouped = collect($evaluation->criteriaForDisplay())->groupBy(fn ($criterion) => $sectionOf[$criterion['label']] ?? 'Lainnya');
        @endphp
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-semibold">{{ $evaluation->evaluator?->name ?: 'Responden' }}</h3>
                    <p class="mt-0.5 text-sm text-muted">{{ $evaluation->program?->businessUnit?->name ?? '—' }} · {{ $evaluation->program?->department?->name ?? '—' }}</p>
                </div>
                <p class="shrink-0 rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-primary-dark">Rata-rata {{ $evaluation->average() }} / 5</p>
            </div>
            @foreach($grouped as $sectionTitle => $items)
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.12em] text-muted">{{ $sectionTitle }}</p>
                <ul class="mt-1.5 space-y-1 text-sm">
                    @foreach($items as $criterion)
                        <li class="flex items-baseline justify-between gap-3 rounded-lg bg-bg/60 px-3 py-2">
                            <span>{{ $criterion['label'] }}</span>
                            <b class="shrink-0">{{ $criterion['score'] }} / 5</b>
                        </li>
                    @endforeach
                </ul>
            @endforeach
            @if(filled($evaluation->comments))
                <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-800"><b>Catatan:</b> {{ $evaluation->comments }}</p>
            @endif
        </article>
    @empty
        <p class="rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada kuesioner peserta yang terisi.</p>
    @endforelse
</div>

<h2 class="mt-8 font-semibold">Semua nilai</h2>
<p class="mt-1 text-xs text-muted">Termasuk nilai raport mentor (skala 0–100) dan kuesioner peserta (skala 1–5).</p>
<div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Penilai</th><th class="px-4 py-3">Rata-rata</th></tr></thead>
        <tbody>
        @foreach($evaluations as $evaluation)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $evaluation->program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $evaluation->evaluator->name }}</td>
                <td class="px-4 py-3">
                    @if($evaluation->hasReport())
                        {{ $evaluation->reportAverage() }} ({{ $evaluation->predicate() }})
                    @else
                        {{ $evaluation->average() }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
