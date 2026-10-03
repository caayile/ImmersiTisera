@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
<div>
    <h1 class="text-2xl font-semibold">Evaluasi Peserta</h1>
    <p class="mt-1 text-sm text-muted">Nilai capaian dosen bimbingan Anda dan lihat penilaian mereka untuk Anda.</p>
</div>
@php
    $reportByProgram = $given->keyBy('program_id');
    $gradeForms = $programs->mapWithKeys(fn ($program) => [$program->id => [
        'url' => route('mentor.evaluations.store', $program),
        'comments' => $reportByProgram->get($program->id)?->comments ?? '',
        'groups' => $reportByProgram->get($program->id)?->grade_groups ?: \App\Models\Evaluation::defaultGradeGroups(),
    ]]);
@endphp
<div class="mt-6" x-data="{
    tab: 'given',
    reportModal: {{ $errors->any() ? 'true' : 'false' }},
    reportForms: @js($gradeForms),
    reportSelected: '{{ $programs->first()?->id }}',
    reportAvg() {
        const form = this.reportForms[this.reportSelected];
        if (!form) return null;
        let weighted = 0, total = 0;
        for (const group of form.groups) {
            const scores = (group.aspects || []).map((a) => parseFloat(a.score)).filter((v) => !Number.isNaN(v));
            if (!scores.length) continue;
            const weight = parseFloat(group.weight) || 0;
            weighted += (scores.reduce((a, b) => a + b, 0) / scores.length) * weight;
            total += weight;
        }
        return total > 0 ? Math.round((weighted / total) * 10) / 10 : null;
    },
    reportPredicate(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (value >= 85) return 'A';
        if (value >= 70) return 'B';
        if (value >= 60) return 'C';
        if (value >= 50) return 'D';
        return 'E';
    },
    reportWeightTotal() {
        const form = this.reportForms[this.reportSelected];
        if (!form) return 0;
        return form.groups.reduce((sum, g) => sum + (parseFloat(g.weight) || 0), 0);
    },
    addReportAspect(gi) {
        const aspects = this.reportForms[this.reportSelected].groups[gi].aspects;
        if (aspects.length < 20) aspects.push({ label: '', score: null });
    },
    removeReportAspect(gi, ai) {
        const aspects = this.reportForms[this.reportSelected].groups[gi].aspects;
        if (aspects.length > 1) aspects.splice(ai, 1);
    },
    addReportGroup() {
        const groups = this.reportForms[this.reportSelected].groups;
        if (groups.length < 10) groups.push({ name: '', weight: 0, aspects: [{ label: '', score: null }] });
    },
    removeReportGroup(gi) {
        const groups = this.reportForms[this.reportSelected].groups;
        if (groups.length > 1) groups.splice(gi, 1);
    },
    openReport(programId) {
        if (programId && this.reportForms[programId]) this.reportSelected = String(programId);
        this.reportModal = true;
    }
}">
    <div class="grid grid-cols-2 gap-1 rounded-xl border border-line bg-white p-1 text-sm font-semibold">
        <button type="button" @click="tab = 'given'" :class="tab === 'given' ? 'rounded-lg bg-primary/12 text-primary-dark' : 'rounded-lg text-muted hover:text-ink'" class="px-4 py-2.5 transition">Penilaian untuk peserta</button>
        <button type="button" @click="tab = 'received'" :class="tab === 'received' ? 'rounded-lg bg-primary/12 text-primary-dark' : 'rounded-lg text-muted hover:text-ink'" class="px-4 py-2.5 transition">Penilaian dari peserta</button>
    </div>

    <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
        <div class="relative flex-1">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
            <input id="eval-search" type="search" placeholder="Cari nama..." class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
        </div>
        @if($programs->isNotEmpty())
            <button type="button" @click="openReport(reportSelected)" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Isi evaluasi
            </button>
        @endif
    </div>

    <div x-show="tab === 'given'">
        <p class="mt-4 text-sm text-muted">Pilih peserta untuk mengisi nilai magang. Setelah disimpan, rata-rata nilai magang muncul di daftar ini.</p>
        <div class="mt-4 space-y-4">
            @forelse($programs as $program)
                @php
                    $participantUser = $program->participant?->user;
                    $programReport = $reportByProgram->get($program->id);
                    $programAvg = $programReport?->reportAverage();
                @endphp
                <article class="eval-row rounded-2xl border border-line bg-white p-5" data-search="{{ strtolower(($participantUser?->name ?: '').' '.($participantUser?->email ?: '').' '.($program->businessUnit?->name ?: '')) }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-semibold">{{ $participantUser?->name ?: 'Peserta' }}</h3>
                            <p class="mt-0.5 truncate text-sm text-muted">{{ $participantUser?->email }}</p>
                            <p class="mt-0.5 text-sm text-primary-dark">{{ $program->businessUnit?->name }}</p>
                            <p class="mt-0.5 text-xs text-muted">Status: {{ $program->status }} · Sejak {{ $program->created_at?->format('d M Y') }}</p>
                        </div>
                        <div class="shrink-0 sm:text-right">
                            @if($programAvg === null)
                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-800">Belum dinilai</span>
                                <div><button type="button" @click="openReport('{{ $program->id }}')" class="mt-2 text-sm font-semibold text-primary-dark hover:underline">Klik untuk input nilai</button></div>
                            @else
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Rata-rata nilai magang</p>
                                <p class="mt-1 text-3xl font-semibold">{{ $programAvg }} <span class="text-lg">{{ $programReport->predicate() }}</span></p>
                                <div><button type="button" @click="openReport('{{ $program->id }}')" class="mt-1 text-xs text-muted hover:text-primary-dark hover:underline">{{ $programReport->updated_at?->diffForHumans() }} · klik untuk ubah</button></div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada peserta bimbingan.</p>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'received'" x-cloak>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead>
                    <tr class="border-b border-line text-sm font-semibold">
                        <th class="px-4 py-3">No.</th>
                        <th class="px-4 py-3">Penilai</th>
                        <th class="px-4 py-3">Indikator penilaian</th>
                        <th class="px-4 py-3">Rata-rata</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($received as $index => $eval)
                        <tr class="eval-row border-b border-line last:border-0" data-search="{{ strtolower($eval->evaluator->name.' '.($eval->comments ?: '')) }}">
                            <td class="row-no px-4 py-3 align-top">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 align-top font-medium">{{ $eval->evaluator->name }}</td>
                            <td class="px-4 py-3 align-top"><div class="space-y-1">@foreach($eval->criteriaForDisplay() as $criterion)<p>{{ $criterion['label'] }}: <b>{{ $criterion['score'] }}/5</b></p>@endforeach</div></td>
                            <td class="px-4 py-3 align-top font-semibold">{{ $eval->average() }} / 5</td>
                            <td class="px-4 py-3 align-top text-muted">{{ $eval->comments ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-muted">Belum ada penilaian dari peserta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p id="eval-empty" class="mt-4 hidden text-center text-sm text-muted">Tidak ada penilaian yang cocok dengan pencarian.</p>

<div id="eval-modal" x-show="reportModal" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="reportModal && 'flex'" @click.self="reportModal = false" @keydown.escape.window="reportModal = false">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-ink">Input Nilai Magang</h3>
                <p class="mt-1 text-xs text-muted">Nilai 0–100 untuk setiap aspek. Rata-rata dihitung otomatis dari bobot kelompok.</p>
            </div>
            <button type="button" @click="reportModal = false" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-line text-muted hover:text-ink">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        @if($errors->any())
            <p class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
        @endif
        <form method="POST" :action="reportForms[reportSelected].url" class="mt-5 space-y-5" id="eval-form">
            @csrf
            <div>
                <label class="text-xs font-medium text-muted">Peserta</label>
                <select x-model="reportSelected" class="mt-1 w-full rounded-xl border border-line px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->participant->user->name }} · {{ $program->businessUnit?->name }}</option>
                    @endforeach
                </select>
            </div>
            <template x-for="(group, gi) in reportForms[reportSelected].groups" :key="gi">
                <div class="rounded-2xl border border-line p-4">
                    <div class="grid gap-3 sm:grid-cols-[1fr_8rem_auto] sm:items-end">
                        <div>
                            <label class="text-xs font-medium text-muted" :for="`rg-name-${gi}`">Kelompok (bobot <span x-text="group.weight"></span>%)</label>
                            <input :id="`rg-name-${gi}`" :name="`groups[${gi}][name]`" x-model="group.name" maxlength="120" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm font-semibold outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted" :for="`rg-weight-${gi}`">Bobot (%)</label>
                            <input :id="`rg-weight-${gi}`" :name="`groups[${gi}][weight]`" x-model="group.weight" type="number" min="0" max="100" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                        </div>
                        <button type="button" @click="removeReportGroup(gi)" :disabled="reportForms[reportSelected].groups.length === 1" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-white text-muted transition hover:border-red-300 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40" aria-label="Hapus kelompok">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                    <div class="mt-3 space-y-2">
                        <template x-for="(aspect, ai) in group.aspects" :key="ai">
                            <div class="grid gap-3 sm:grid-cols-[1fr_7rem_auto] sm:items-end">
                                <input :name="`groups[${gi}][aspects][${ai}][label]`" x-model="aspect.label" maxlength="120" placeholder="Nama aspek" class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                                <input :name="`groups[${gi}][aspects][${ai}][score]`" x-model="aspect.score" type="number" min="0" max="100" placeholder="Nilai" class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                                <button type="button" @click="removeReportAspect(gi, ai)" :disabled="group.aspects.length === 1" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-white text-xs font-semibold text-red-600 transition hover:border-red-300 disabled:cursor-not-allowed disabled:opacity-40">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addReportAspect(gi)" class="mt-3 inline-flex items-center gap-1.5 rounded-xl border border-primary/30 px-3 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Tambah aspek
                    </button>
                </div>
            </template>
            <button type="button" @click="addReportGroup()" class="inline-flex items-center gap-1.5 rounded-xl border border-line px-3 py-2 text-sm font-semibold text-muted transition hover:border-primary hover:text-primary-dark">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah kelompok
            </button>
            <div class="grid gap-3 rounded-2xl bg-[#eef6f2] p-4 sm:grid-cols-[auto_1fr] sm:items-center">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Rata-rata nilai magang (otomatis)</p>
                    <p class="mt-1 text-3xl font-semibold"><span x-text="reportAvg() ?? '—'"></span> <span class="text-lg" x-text="reportPredicate(reportAvg())"></span></p>
                    <p class="mt-1 text-xs text-muted">Rumus: Σ(rata-rata kelompok × bobot) ÷ total bobot (<span x-text="reportWeightTotal()"></span>%)</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Catatan (opsional)</label>
                    <textarea name="comments" rows="3" x-model="reportForms[reportSelected].comments" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Catatan untuk peserta"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" @click="reportModal = false" class="rounded-xl border border-line px-4 py-2.5 text-sm font-semibold text-muted">Kembali ke daftar</button>
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan nilai</button>
            </div>
        </form>
    </div>
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
})();
</script>
@endsection
