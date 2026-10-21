@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
@php
    $scaleLabels = collect($questionnaire['scales'])->mapWithKeys(fn (array $scale): array => [(int) $scale['value'] => $scale['label']])->all();
    $formatScore = function (?float $value) use ($scaleLabels): string {
        if ($value === null) {
            return '—';
        }

        $number = number_format($value, 1);
        $label = $scaleLabels[(int) round($value)] ?? null;

        return $label === null ? $number : $number.' · '.$label;
    };
    $sectionColor = [
        'program' => '#5EC69D',
        'departemen' => '#38bdf8',
        'mentor' => '#f59e0b',
        'lainnya' => '#94a3b8',
    ];
    $questionColor = function (string $section) use ($sectionColor): string {
        $key = match ($section) {
            'Program' => 'program',
            'Departemen' => 'departemen',
            'Mentor' => 'mentor',
            default => 'lainnya',
        };

        return $sectionColor[$key];
    };
    $filledSections = collect($questionnaire['sections'])->filter(fn (array $section): bool => $section['average'] !== null)->values();
    $sectionChart = [
        'labels' => $filledSections->pluck('label')->all(),
        'values' => $filledSections->pluck('average')->all(),
        'colors' => $filledSections->map(fn (array $section): string => $sectionColor[$section['key']] ?? '#94a3b8')->all(),
    ];
    $barWidth = function (float $score) use ($questionnaire): float {
        $max = max(1, (int) $questionnaire['scale_max']);

        return min(100, round(($score / $max) * 100, 1));
    };
    $scoreTone = function (float $score): string {
        $rounded = (int) round($score);

        if ($rounded <= 2) {
            return 'bg-red-50 text-red-700';
        }

        if ($rounded === 3) {
            return 'bg-amber-50 text-amber-800';
        }

        return 'bg-emerald-50 text-emerald-700';
    };
    $questionChart = [
        'labels' => collect($questionnaire['questions'])->pluck('label')->all(),
        'values' => collect($questionnaire['questions'])->pluck('average')->all(),
        'colors' => collect($questionnaire['questions'])->map(fn (array $question): string => $questionColor($question['section']))->all(),
    ];
    $programChart = [
        'labels' => collect($questionnaire['by_program'])->pluck('label')->all(),
        'values' => collect($questionnaire['by_program'])->pluck('average')->all(),
    ];
    $departemenChart = [
        'labels' => collect($questionnaire['by_departemen'])->pluck('label')->all(),
        'values' => collect($questionnaire['by_departemen'])->pluck('average')->all(),
    ];
    $mentorChart = [
        'labels' => collect($questionnaire['by_mentor'])->pluck('label')->all(),
        'values' => collect($questionnaire['by_mentor'])->pluck('average')->all(),
    ];
    $filtersActive = request()->filled('program_id') || request()->filled('business_unit_id') || request()->filled('mentor_id');
@endphp

<section>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-semibold">Laporan kuesioner</h2>
            <p class="mt-1 max-w-2xl text-sm text-muted">Siapa yang sudah mengisi, berapa nilainya, dan apa kritiknya. Angka 1 berarti sangat tidak setuju, angka 5 berarti sangat setuju.</p>
        </div>
        <a href="{{ route('admin.reports', array_filter(['export' => 'kuesioner', 'program_id' => request('program_id'), 'business_unit_id' => request('business_unit_id'), 'mentor_id' => request('mentor_id')])) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Ekspor kuesioner</a>
    </div>
    <ul class="mt-4 flex flex-wrap gap-2">
        @foreach($questionnaire['scales'] as $scale)
            <li class="rounded-full border border-line bg-white px-3 py-1 text-xs text-muted"><b class="text-ink">{{ $scale['value'] }}</b> {{ $scale['label'] }}</li>
        @endforeach
    </ul>

    <form method="GET" class="mt-4 grid gap-3 rounded-2xl border border-line bg-white p-4 md:grid-cols-3">
        <label class="grid gap-1 text-xs font-semibold text-muted">
            Program
            <select name="program_id" onchange="this.form.submit()" aria-label="Program" class="rounded-lg border border-line bg-white px-3 py-2 text-sm font-normal text-ink">
                <option value="">Semua program</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) request('program_id') === (string) $program->id)>
                        {{ $program->participant?->user?->name ?? 'Program #'.$program->id }} — {{ $program->businessUnit?->name ?? '—' }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-xs font-semibold text-muted">
            Departemen
            <select name="business_unit_id" onchange="this.form.submit()" aria-label="Departemen" class="rounded-lg border border-line bg-white px-3 py-2 text-sm font-normal text-ink">
                <option value="">Semua departemen</option>
                @foreach($businessUnits as $unit)
                    <option value="{{ $unit->id }}" @selected((string) request('business_unit_id') === (string) $unit->id)>
                        {{ $unit->name }}@if($unit->department) · {{ $unit->department->name }}@endif
                    </option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-xs font-semibold text-muted">
            Mentor
            <select name="mentor_id" onchange="this.form.submit()" aria-label="Mentor" class="rounded-lg border border-line bg-white px-3 py-2 text-sm font-normal text-ink">
                <option value="">Semua mentor</option>
                @foreach($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected((string) request('mentor_id') === (string) $mentor->id)>{{ $mentor->user?->name ?? 'Mentor #'.$mentor->id }}</option>
                @endforeach
            </select>
        </label>
        @if($filtersActive)
            <a href="{{ route('admin.reports') }}" class="text-sm font-semibold text-primary-dark hover:underline md:col-span-3">Reset filter</a>
        @endif
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <article class="rounded-2xl border border-line bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Jumlah pengisi</p>
            <p class="mt-2 text-2xl font-semibold">{{ $questionnaire['respondents'] }}</p>
            <p class="mt-1 text-xs text-muted">Peserta yang sudah menyimpan kuesioner.</p>
        </article>
        <article class="rounded-2xl border border-line bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Rata-rata</p>
            <p class="mt-2 text-2xl font-semibold">{{ $formatScore($questionnaire['overall']) }}</p>
            <p class="mt-1 text-xs text-muted">Gabungan semua pernyataan yang dijawab.</p>
        </article>
        <article class="rounded-2xl border border-line bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Paling rendah</p>
            @if($questionnaire['lowest'])
                <p class="mt-2 line-clamp-3 text-sm font-semibold">{{ $questionnaire['lowest']['label'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $formatScore($questionnaire['lowest']['average']) }}</p>
            @else
                <p class="mt-2 text-2xl font-semibold text-muted">—</p>
            @endif
        </article>
    </div>

    @if($questionnaire['respondents'] === 0)
        <p class="mt-6 rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada kuesioner peserta yang terisi.</p>
    @else
        <h3 class="mt-8 font-semibold">Pengisi kuesioner</h3>
        <p class="mt-1 text-sm text-muted">Satu kartu untuk setiap peserta. Hijau berarti setuju, merah berarti tidak setuju.</p>
        <div class="mt-3 grid gap-4">
            @foreach($questionnaire['rows'] as $row)
                <article class="rounded-2xl border border-line bg-white p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="text-base font-semibold">{{ $row['participant'] }}</h4>
                            <p class="mt-1 text-sm text-muted">{{ $row['unit_bisnis'] }} · {{ $row['departemen'] }} · Mentor {{ $row['mentor'] }}</p>
                        </div>
                        <p class="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary-dark">Rata-rata {{ $formatScore($row['average']) }}</p>
                    </div>
                    @if($row['scores']['program'] !== null || $row['scores']['departemen'] !== null || $row['scores']['mentor'] !== null)
                        <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                            @foreach(['program' => 'Program', 'departemen' => 'Departemen', 'mentor' => 'Mentor'] as $key => $label)
                                <div class="rounded-xl bg-bg px-3 py-2">
                                    <dt class="text-xs text-muted">{{ $label }}</dt>
                                    <dd class="mt-1 text-sm font-semibold">{{ $formatScore($row['scores'][$key]) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                    <p class="mt-4 text-sm"><span class="font-semibold">Kritik dan saran.</span> {{ filled($row['comments']) ? $row['comments'] : 'Tidak ada.' }}</p>
                    <div class="mt-4 grid gap-4">
                        @foreach(collect($row['answers'])->groupBy('section') as $section => $answers)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $section }}</p>
                                <ul class="mt-2 grid gap-2">
                                    @foreach($answers as $answer)
                                        <li class="rounded-xl border border-line px-3 py-2">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-sm">{{ $answer['label'] }}</p>
                                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $scoreTone($answer['score']) }}">{{ $formatScore($answer['score']) }}</span>
                                            </div>
                                            <div class="mt-2 h-1.5 rounded-full bg-bg">
                                                <div class="h-1.5 rounded-full bg-primary" style="width: {{ $barWidth($answer['score']) }}%"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        <h3 class="mt-8 font-semibold">Rata-rata per bagian</h3>
        <p class="mt-1 text-sm text-muted">Makin panjang batang, makin setuju peserta pada bagian itu.</p>
        <div class="mt-3 grid gap-4 sm:grid-cols-3">
            @foreach($questionnaire['sections'] as $section)
                @continue($section['average'] === null)
                <article class="rounded-2xl border border-line bg-white p-5">
                    <p class="text-sm font-semibold">{{ $section['label'] }}</p>
                    <p class="mt-2 text-lg font-semibold">{{ $formatScore($section['average']) }}</p>
                    <div class="mt-3 h-2 rounded-full bg-bg">
                        <div class="h-2 rounded-full" style="width: {{ $barWidth($section['average']) }}%; background: {{ $sectionColor[$section['key']] ?? '#94a3b8' }}"></div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-line bg-white p-5">
                <h3 class="font-semibold">Grafik per bagian</h3>
                <div class="relative mt-4 h-64">
                    <canvas id="questionnaireSectionChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-line bg-white p-5">
                <h3 class="font-semibold">Grafik per pernyataan</h3>
                <ul class="mt-2 flex flex-wrap gap-3 text-xs text-muted">
                    <li class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-primary"></span> Program</li>
                    <li class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span> Departemen</li>
                    <li class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Mentor</li>
                </ul>
                <div class="relative mt-4" style="height: {{ max(240, count($questionnaire['questions']) * 36) }}px">
                    <canvas id="questionnaireQuestionChart"></canvas>
                </div>
            </article>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <article class="rounded-2xl border border-line bg-white p-5">
                <h3 class="font-semibold">Per program</h3>
                <p class="mt-1 text-xs text-muted">Rata-rata seluruh jawaban pada program itu.</p>
                <div class="relative mt-4" style="height: {{ max(200, count($questionnaire['by_program']) * 36) }}px">
                    <canvas id="questionnaireProgramChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-line bg-white p-5">
                <h3 class="font-semibold">Per departemen</h3>
                <p class="mt-1 text-xs text-muted">Rata-rata seluruh jawaban pada departemen itu.</p>
                <div class="relative mt-4" style="height: {{ max(200, count($questionnaire['by_departemen']) * 36) }}px">
                    <canvas id="questionnaireDepartemenChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-line bg-white p-5">
                <h3 class="font-semibold">Per mentor</h3>
                <p class="mt-1 text-xs text-muted">Rata-rata seluruh jawaban pada mentor itu.</p>
                <div class="relative mt-4" style="height: {{ max(200, count($questionnaire['by_mentor']) * 36) }}px">
                    <canvas id="questionnaireMentorChart"></canvas>
                </div>
            </article>
        </div>

        <h3 class="mt-8 font-semibold">Rata-rata per pernyataan</h3>
        <p class="mt-1 text-sm text-muted">Pernyataan dengan nilai paling rendah ditandai merah.</p>
        <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-bg text-xs uppercase text-muted">
                    <tr>
                        <th class="px-4 py-3">Bagian</th>
                        <th class="px-4 py-3">Pernyataan</th>
                        <th class="px-4 py-3">Pengisi</th>
                        <th class="px-4 py-3">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questionnaire['questions'] as $question)
                        <tr class="border-t border-line">
                            <td class="px-4 py-3">{{ $question['section'] }}</td>
                            <td class="px-4 py-3">{{ $question['label'] }}</td>
                            <td class="px-4 py-3">{{ $question['respondents'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="hidden h-1.5 w-24 rounded-full bg-bg sm:block">
                                        <div class="h-1.5 rounded-full {{ $questionnaire['lowest'] && $question['label'] === $questionnaire['lowest']['label'] ? 'bg-red-500' : 'bg-primary' }}" style="width: {{ $barWidth($question['average']) }}%"></div>
                                    </div>
                                    @if($questionnaire['lowest'] && $question['label'] === $questionnaire['lowest']['label'])
                                        <span class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-700">{{ $formatScore($question['average']) }}</span>
                                    @else
                                        {{ $formatScore($question['average']) }}
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@if($questionnaire['respondents'] > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const questionnaireScaleMax = @json($questionnaire['scale_max']);
    const questionnaireAxis = {
        min: 0,
        max: questionnaireScaleMax,
        ticks: { stepSize: 1 },
    };

    const horizontalScoreChart = (canvasId, labels, values, colors) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas || labels.length === 0) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 6,
                    maxBarThickness: 22,
                }],
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: questionnaireAxis },
            },
        });
    };

    new Chart(document.getElementById('questionnaireSectionChart'), {
        type: 'bar',
        data: {
            labels: @json($sectionChart['labels']),
            datasets: [{
                data: @json($sectionChart['values']),
                backgroundColor: @json($sectionChart['colors']),
                borderRadius: 6,
                maxBarThickness: 48,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: questionnaireAxis },
        },
    });

    horizontalScoreChart(
        'questionnaireQuestionChart',
        @json($questionChart['labels']),
        @json($questionChart['values']),
        @json($questionChart['colors']),
    );

    horizontalScoreChart(
        'questionnaireProgramChart',
        @json($programChart['labels']),
        @json($programChart['values']),
        '#5EC69D',
    );
    horizontalScoreChart(
        'questionnaireDepartemenChart',
        @json($departemenChart['labels']),
        @json($departemenChart['values']),
        '#38bdf8',
    );
    horizontalScoreChart(
        'questionnaireMentorChart',
        @json($mentorChart['labels']),
        @json($mentorChart['values']),
        '#f59e0b',
    );
</script>
@endpush
@endif
