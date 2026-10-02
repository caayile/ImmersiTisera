@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
@php
    $myEvaluations = $program ? $program->evaluations->where('evaluator_id', auth()->id())->values() : collect();
    $mentorEvaluations = $program ? $program->evaluations->whereNotIn('evaluator_id', [auth()->id()])->values() : collect();
    $columnLabels = function ($evaluations) {
        $labels = $evaluations->flatMap(fn ($eval) => collect($eval->criteriaForDisplay())->pluck('label'))->unique()->values();
        return $labels->isNotEmpty() ? $labels : collect((new \App\Models\Evaluation)->criteriaForDisplay())->pluck('label');
    };
    $myColumns = $columnLabels($myEvaluations);
    $mentorColumns = $columnLabels($mentorEvaluations);
@endphp
<div>
    <h1 class="text-2xl font-semibold">Evaluasi</h1>
    <p class="mt-1 text-sm text-muted">Penilaian dua arah di akhir program: dosen menilai pelaksanaan magang dan mentor menilai capaian dosen, sebagai syarat penyelesaian program.</p>
</div>
@unless($program)
    <x-empty class="mt-6" title="Evaluasi belum dibuka" />
@else
<div class="mt-6" x-data="{ tab: 'mine' }">
    <div class="grid grid-cols-2 gap-1 rounded-xl border border-line bg-white p-1 text-sm font-semibold">
        <button type="button" @click="tab = 'mine'" :class="tab === 'mine' ? 'rounded-lg bg-primary/12 text-primary-dark' : 'rounded-lg text-muted hover:text-ink'" class="px-4 py-2.5 transition">Penilaian untuk mentor</button>
        <button type="button" @click="tab = 'mentor'" :class="tab === 'mentor' ? 'rounded-lg bg-primary/12 text-primary-dark' : 'rounded-lg text-muted hover:text-ink'" class="px-4 py-2.5 transition">Penilaian dari mentor</button>
    </div>

    <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
        <div class="relative flex-1">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
            <input id="eval-search" type="search" placeholder="Cari penilaian..." class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
        </div>
        <button id="eval-modal-open" type="button" x-show="tab === 'mine'" x-cloak class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Isi evaluasi
        </button>
    </div>

    <div x-show="tab === 'mine'">
        <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead>
                    <tr class="border-b border-line text-sm font-semibold">
                        <th class="px-4 py-3">No.</th>
                        @foreach($myColumns as $label)
                            <th class="px-4 py-3">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3">Rata-rata</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myEvaluations as $index => $eval)
                        @php $myScores = collect($eval->criteriaForDisplay())->keyBy('label'); @endphp
                        <tr class="eval-row border-b border-line last:border-0" data-search="{{ strtolower($myScores->map(fn ($c) => $c['label'].' '.$c['score'])->implode(' ').' '.($eval->comments ?: '')) }}">
                            <td class="row-no px-4 py-3 align-top">{{ $index + 1 }}</td>
                            @foreach($myColumns as $label)
                                <td class="px-4 py-3 align-top">{{ $myScores->has($label) ? $myScores[$label]['score'].' / 5' : '—' }}</td>
                            @endforeach
                            <td class="px-4 py-3 align-top font-semibold">{{ $eval->average() }} / 5</td>
                            <td class="px-4 py-3 align-top text-muted">{{ $eval->comments ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $myColumns->count() + 3 }}" class="px-4 py-8 text-center text-sm text-muted">Belum ada penilaian untuk mentor. Klik Isi evaluasi untuk mulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="tab === 'mentor'" x-cloak>
        @php
            $reportEvaluations = $mentorEvaluations->filter(fn ($eval) => $eval->hasReport())->values();
            $legacyEvaluations = $mentorEvaluations->reject(fn ($eval) => $eval->hasReport())->values();
            $reportLocked = $reportEvaluations->isNotEmpty() && $myEvaluations->isEmpty();
        @endphp
        @if($reportLocked)
            <div class="mt-4 rounded-2xl border border-line bg-white p-6 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 text-primary-dark">
                    <span class="material-symbols-outlined text-[26px]">lock</span>
                </span>
                <h3 class="mt-3 font-semibold">Nilai raport dari mentor terkunci</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-muted">Mentor sudah memberikan nilai raport. Isi evaluasi untuk mentor terlebih dahulu untuk membuka nilaimu.</p>
                <button type="button" @click="tab = 'mine'" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Isi evaluasi
                </button>
            </div>
        @else
            @foreach($reportEvaluations as $eval)
                <article class="eval-row mt-4 overflow-hidden rounded-2xl border border-line bg-white" data-search="{{ strtolower($eval->evaluator->name.' raport '.($eval->comments ?: '')) }}">
                    <div class="flex flex-col gap-1 border-b border-line bg-[#f4f8f6] p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nilai raport dari {{ $eval->evaluator->name }}</p>
                            <p class="mt-1 text-3xl font-semibold">{{ $eval->reportAverage() }} <span class="text-lg text-primary-dark">{{ $eval->predicate() }}</span></p>
                        </div>
                        @if(filled($eval->comments))
                            <p class="max-w-md text-sm text-muted">{{ $eval->comments }}</p>
                        @endif
                    </div>
                    @foreach($eval->grade_groups as $group)
                        @php $groupScores = collect($group['aspects'] ?? [])->map(fn ($a) => (float) ($a['score'] ?? 0)); @endphp
                        <div class="border-b border-line p-5 last:border-0">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="font-semibold">{{ $group['name'] }} <span class="text-xs font-medium text-muted">(bobot {{ $group['weight'] }}%)</span></h4>
                                <p class="text-sm font-semibold">{{ $groupScores->isNotEmpty() ? round($groupScores->avg(), 1) : '—' }}</p>
                            </div>
                            <dl class="mt-3 space-y-2">
                                @foreach($group['aspects'] ?? [] as $aspect)
                                    <div class="flex items-center justify-between gap-3 rounded-xl bg-bg/60 px-3 py-2 text-sm">
                                        <dt class="text-muted">{{ $aspect['label'] }}</dt>
                                        <dd class="font-semibold">{{ $aspect['score'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </article>
            @endforeach
            @if($legacyEvaluations->isNotEmpty())
                <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white">
                    <table class="w-full min-w-[860px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-line text-sm font-semibold">
                                <th class="px-4 py-3">No.</th>
                                <th class="px-4 py-3">Penilai</th>
                                @foreach($mentorColumns as $label)
                                    <th class="px-4 py-3">{{ $label }}</th>
                                @endforeach
                                <th class="px-4 py-3">Rata-rata</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($legacyEvaluations as $index => $eval)
                                @php $mentorScores = collect($eval->criteriaForDisplay())->keyBy('label'); @endphp
                                <tr class="eval-row border-b border-line last:border-0" data-search="{{ strtolower($eval->evaluator->name.' '.$mentorScores->map(fn ($c) => $c['label'].' '.$c['score'])->implode(' ').' '.($eval->comments ?: '')) }}">
                                    <td class="row-no px-4 py-3 align-top">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 align-top font-medium">{{ $eval->evaluator->name }}</td>
                                    @foreach($mentorColumns as $label)
                                        <td class="px-4 py-3 align-top">{{ $mentorScores->has($label) ? $mentorScores[$label]['score'].' / 5' : '—' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 align-top font-semibold">{{ $eval->average() }} / 5</td>
                                    <td class="px-4 py-3 align-top text-muted">{{ $eval->comments ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if($mentorEvaluations->isEmpty())
                <p class="mt-4 rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada penilaian dari mentor.</p>
            @endif
        @endif
    </div>
    <p id="eval-empty" class="mt-4 hidden text-center text-sm text-muted">Tidak ada penilaian yang cocok dengan pencarian.</p>
</div>

<div id="eval-modal" class="{{ $errors->any() ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-ink">Form Evaluasi</h3>
                <p class="mt-1 text-xs text-muted">Nilai 1–5 untuk setiap aspek.</p>
            </div>
            <button type="button" id="eval-modal-x" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-line text-muted hover:text-ink">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        @if($errors->any())
            <p class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
        @endif
        <form method="POST" class="mt-5 space-y-4">
            @csrf
            <x-evaluation-criteria-fields :criteria="$mine?->criteriaForDisplay() ?? []" />
            <textarea name="comments" rows="3" class="w-full rounded-xl border border-line px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Catatan">{{ old('comments') }}</textarea>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" id="eval-modal-cancel" class="rounded-xl border border-line px-4 py-2.5 text-sm font-semibold text-muted">Batal</button>
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan evaluasi</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var search = document.getElementById('eval-search');
    var emptyMsg = document.getElementById('eval-empty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.eval-row'));

    function render() {
        var query = search ? search.value.trim().toLowerCase() : '';
        var visible = 0;
        var counters = {};
        rows.forEach(function (row) {
            var show = !query || row.dataset.search.indexOf(query) !== -1;
            row.classList.toggle('hidden', !show);
            if (show) {
                var tbody = row.closest('tbody');
                var key = tbody ? Array.prototype.indexOf.call(tbody.parentNode.querySelectorAll('tbody'), tbody) : 0;
                counters[key] = (counters[key] || 0) + 1;
                var no = row.querySelector('.row-no');
                if (no) no.textContent = counters[key];
                visible += 1;
            }
        });
        if (emptyMsg) emptyMsg.classList.toggle('hidden', visible > 0);
    }

    if (search) search.addEventListener('input', render);

    var modal = document.getElementById('eval-modal');
    var openBtn = document.getElementById('eval-modal-open');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (openBtn) openBtn.addEventListener('click', openModal);

    function wireClose(id) {
        var button = document.getElementById(id);
        if (button) button.addEventListener('click', closeModal);
    }

    wireClose('eval-modal-x');
    wireClose('eval-modal-cancel');

    if (modal) {
        var downTarget = null;
        modal.addEventListener('mousedown', function (event) {
            downTarget = event.target;
        });
        modal.addEventListener('click', function (event) {
            if (event.target === modal && downTarget === modal) closeModal();
            downTarget = null;
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
@endunless
@endsection
