@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
<p class="text-sm text-muted">Umpan balik kuesioner peserta untuk Anda dan unit bisnis — dikelompokkan per bagian penilaian.</p>
@php
    $sectionOf = [];
    foreach ($bank as $section) {
        foreach ($section['questions'] as $question) {
            $sectionOf[$question] = $section['title'];
        }
    }
@endphp
<div class="mt-6">
    <div class="relative">
        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
        <input id="eval-search" type="search" placeholder="Cari nama..." class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
    </div>

    <div class="mt-4 space-y-4">
        @forelse($received as $eval)
            @php
                $grouped = collect($eval->criteriaForDisplay())->groupBy(fn ($criterion) => $sectionOf[$criterion['label']] ?? 'Lainnya');
            @endphp
            <article class="eval-row rounded-2xl border border-line bg-white p-5" data-search="{{ strtolower($eval->evaluator->name.' '.($eval->comments ?: '')) }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="font-semibold">{{ $eval->evaluator->name }}</h3>
                        <p class="mt-0.5 text-sm text-muted">{{ $eval->program?->businessUnit?->name ?? '—' }} · {{ $eval->program?->department?->name ?? '—' }}</p>
                    </div>
                    <p class="shrink-0 rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-primary-dark">Rata-rata {{ $eval->average() }} · {{ $scaleLabels[(int) round($eval->average())] ?? '—' }}</p>
                </div>
                @foreach($grouped as $sectionTitle => $items)
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.12em] text-muted">{{ $sectionTitle }}</p>
                    <ul class="mt-1.5 space-y-1 text-sm">
                        @foreach($items as $criterion)
                            <li class="flex items-baseline justify-between gap-3 rounded-lg bg-bg/60 px-3 py-2">
                                <span>{{ $criterion['label'] }}</span>
                                <b class="shrink-0">{{ $criterion['score'] }} · {{ $scaleLabels[(int) $criterion['score']] ?? '—' }}</b>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
                @if(filled($eval->comments))
                    <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-800"><b>Catatan:</b> {{ $eval->comments }}</p>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada penilaian dari peserta.</p>
        @endforelse
    </div>
    <p id="eval-empty" class="mt-4 hidden text-center text-sm text-muted">Tidak ada penilaian yang cocok dengan pencarian.</p>
</div>

<script>
(function () {
    var search = document.getElementById('eval-search');
    var emptyMsg = document.getElementById('eval-empty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.eval-row'));

    function render() {
        var query = search ? search.value.trim().toLowerCase() : '';
        var visible = 0;
        rows.forEach(function (row) {
            var show = !query || row.dataset.search.indexOf(query) !== -1;
            row.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });
        if (emptyMsg) emptyMsg.classList.toggle('hidden', visible > 0);
    }

    if (search) search.addEventListener('input', render);
})();
</script>
@endsection
