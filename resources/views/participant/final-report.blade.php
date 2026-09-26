@extends('layouts.app')
@section('title', 'Laporan Akhir')
@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Laporan Akhir</h1>
        <p class="mt-1 text-sm text-muted">Laporan akhir terkait agreement dan output utama program.</p>
    </div>
    @if($program)
        <button id="report-modal-open" type="button" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Laporan akhir
        </button>
    @endif
</div>
@unless($program)
    <x-empty class="mt-6" title="Laporan akhir belum tersedia" />
@else
<div id="report-modal" class="{{ $errors->any() ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-ink">Form Laporan Akhir</h3>
                <p class="mt-1 text-xs text-muted">Lengkapi metadata katalog laporan siklus berjalan.</p>
            </div>
            <button type="button" id="report-modal-x" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-line text-muted hover:text-ink">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        @if($errors->any())
            <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
        @endif
<form method="POST" action="{{ route('participant.final-report') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
    @csrf
    <input type="hidden" name="is_final_report" value="1">
    <input type="hidden" name="type" value="Research Report">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-xs font-medium text-muted">Unit Bisnis</label>
            <select name="department_id" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
                <option value="">— Pilih unit bisnis —</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((int) old('department_id') === (int) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-muted">Departement</label>
            <select name="business_unit_id" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
                <option value="">— Pilih departement —</option>
                @foreach($businessUnits->groupBy(fn ($unit) => $unit->department?->name ?: 'Lainnya') as $groupName => $groupUnits)
                    <optgroup label="{{ $groupName }}">
                        @foreach($groupUnits as $unit)
                            <option value="{{ $unit->id }}" @selected((int) old('business_unit_id') === (int) $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="text-xs font-medium text-muted">Judul Laporan</label>
        <textarea name="title" rows="3" placeholder="Masukkan Judul Laporan" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('title') }}</textarea>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-xs font-medium text-muted">Tahun</label>
            <input name="year" inputmode="numeric" maxlength="4" placeholder="cth. 2026" value="{{ old('year', $program->end_date?->format('Y') ?? $program->start_date?->format('Y') ?? now()->format('Y')) }}" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-muted">Level Kolaborasi</label>
            <select name="level" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
                <option value="">— Pilih level —</option>
                @for($level = 0; $level <= 4; $level++)
                    <option value="{{ $level }}" @selected((string) old('level') === (string) $level)>Level {{ $level }} — {{ \App\Support\Status::COLLABORATION_LEVELS[$level] }}: {{ \App\Support\Status::COLLABORATION_LEVEL_DESCRIPTIONS[$level] }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div>
        <label class="text-xs font-medium text-muted">Hasil</label>
        <p class="mt-0.5 text-[11px] text-muted">Pilih salah satu.</p>
        <input id="hasil_link" name="link" placeholder="cth. https://..." value="{{ old('link') }}" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm disabled:opacity-50">
        <div class="mt-2 flex items-center gap-3">
            <label for="hasil_file" class="cursor-pointer whitespace-nowrap rounded-lg bg-primary/12 px-4 py-2 text-xs font-semibold text-primary-dark transition hover:bg-primary hover:text-white">Pilih file</label>
            <span id="hasil_file_name" class="min-w-0 truncate text-xs text-muted">Belum ada file dipilih</span>
            <input id="hasil_file" type="file" name="hasil_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.zip" class="hidden">
        </div>
    </div>
    <div>
        <label class="text-xs font-medium text-muted">Laporan</label>
        <p class="mt-0.5 text-[11px] text-muted">Pilih salah satu.</p>
        <input id="laporan_link" name="laporan_link" placeholder="cth. https://..." value="{{ old('laporan_link') }}" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm disabled:opacity-50">
        <div class="mt-2 flex items-center gap-3">
            <label for="report_file" class="cursor-pointer whitespace-nowrap rounded-lg bg-primary/12 px-4 py-2 text-xs font-semibold text-primary-dark transition hover:bg-primary hover:text-white">Pilih file</label>
            <span id="report_file_name" class="min-w-0 truncate text-xs text-muted">Belum ada file dipilih</span>
            <input id="report_file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.zip" class="hidden">
        </div>
    </div>
    <div class="flex justify-end gap-3 pt-1">
        <button type="button" id="report-modal-cancel" class="rounded-lg border border-line px-4 py-2.5 text-sm font-semibold text-muted">Batal</button>
        <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Kirim laporan akhir</button>
    </div>
</form>
    </div>
</div>

<div class="mt-6">
    @php
        $reportYears = $programs->flatMap(fn ($cycle) => [
            $cycle->outputs->firstWhere('is_final_report', true)?->year,
            $cycle->end_date?->format('Y'),
            $cycle->start_date?->format('Y'),
        ])->filter()->unique()->sortDesc()->values();
    @endphp
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        <div class="relative flex-1">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
            <input id="report-search" type="search" placeholder="Cari judul laporan / unit bisnis..." class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
        </div>
        <select id="report-year" class="rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold md:w-auto">
            <option value="">Semua Tahun</option>
            @foreach($reportYears as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead>
                <tr class="border-b border-line text-sm font-semibold">
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">Unit Bisnis</th>
                    <th class="px-4 py-3">Departement</th>
                    <th class="px-4 py-3">Judul Laporan</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Hasil</th>
                    <th class="px-4 py-3">Laporan</th>
                    <th class="px-4 py-3">Level</th>
                </tr>
            </thead>
            <tbody id="report-rows">
                @foreach($programs as $index => $cycle)
                    @php
                        $cycleReport = $cycle->outputs->firstWhere('is_final_report', true);
                        $mainOutput = $cycle->outputs->where('is_final_report', false)->firstWhere('is_main_output', true);
                        $rowUnitBisnis = $cycleReport?->department?->name ?? $cycle->department?->name;
                        $rowDepartement = $cycleReport?->businessUnit?->name ?? $cycle->businessUnit?->name;
                        $cycleYear = $cycleReport?->year ?? $cycle->end_date?->format('Y') ?? $cycle->start_date?->format('Y') ?? $cycleReport?->created_at?->format('Y') ?? '';
                    @endphp
                    <tr class="report-row border-b border-line last:border-0"
                        data-search="{{ strtolower(($cycleReport?->title ?: '').' '.($rowUnitBisnis ?: '').' '.($rowDepartement ?: '')) }}"
                        data-year="{{ $cycleYear }}">
                        <td class="row-no px-4 py-3 align-top">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 align-top font-medium">{{ $rowUnitBisnis ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">{{ $rowDepartement ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">
                            <span class="font-medium">{{ $cycleReport?->title ?: 'Laporan Akhir — '.($cycle->businessUnit?->name ?: 'Unit Bisnis') }}</span>
                            <span class="mt-1 block text-xs text-muted">
                                @if($cycle->start_date || $cycle->end_date)
                                    {{ $cycle->start_date?->format('M Y') ?: '—' }} – {{ $cycle->end_date?->format('M Y') ?: '—' }}
                                @endif
                                @if($cycle->id === $program->id)
                                    · <span class="font-semibold text-primary-dark">Siklus berjalan</span>
                                @endif
                                @if(! $cycleReport)
                                    · <span class="font-semibold text-muted">Belum ada laporan</span>
                                @endif
                            </span>
                            @if($cycleReport)
                                <span class="mt-2 block"><x-badge :status="$cycleReport->status" :label="\App\Support\Status::outputLabel($cycleReport->status)" /></span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">{{ $cycleYear ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">
                            @if($cycleReport && $cycleReport->linkUrl())
                                <a href="{{ $cycleReport->linkUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-dark">Lihat Hasil</a>
                            @elseif($cycleReport && $cycleReport->hasilFileUrl())
                                <a href="{{ $cycleReport->hasilFileUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-dark">Lihat Hasil</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($cycleReport && $cycleReport->laporanFileUrl())
                                <a href="{{ $cycleReport->laporanFileUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-dark">Lihat Laporan</a>
                            @elseif($cycleReport && $cycleReport->laporanLinkUrl())
                                <a href="{{ $cycleReport->laporanLinkUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-dark">Lihat Laporan</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($cycle->collaboration)
                                <span class="inline-flex whitespace-nowrap rounded-full bg-primary/12 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">L{{ $cycle->collaboration->level }} · {{ \App\Support\Status::COLLABORATION_LEVELS[$cycle->collaboration->level] ?? '' }}</span>
                                @if($cycle->collaboration->collaboration_type)
                                    <span class="mt-1 block text-xs text-muted">{{ $cycle->collaboration->collaboration_type }}</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p id="report-empty" class="mt-4 hidden text-center text-sm text-muted">Tidak ada laporan yang cocok dengan pencarian.</p>
</div>

<script>
(function () {
    var search = document.getElementById('report-search');
    var year = document.getElementById('report-year');
    var emptyMsg = document.getElementById('report-empty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.report-row'));
    if (!rows.length || !search) return;

    function render() {
        var query = search.value.trim().toLowerCase();
        var visible = 0;
        rows.forEach(function (row) {
            var okSearch = !query || row.dataset.search.indexOf(query) !== -1;
            var okYear = !year.value || row.dataset.year === year.value;
            var show = okSearch && okYear;
            row.classList.toggle('hidden', !show);
            if (show) {
                visible += 1;
                var no = row.querySelector('.row-no');
                if (no) no.textContent = visible;
            }
        });
        emptyMsg.classList.toggle('hidden', visible > 0);
    }

    search.addEventListener('input', render);
    year.addEventListener('change', render);
})();

(function () {
    var openBtn = document.getElementById('report-modal-open');
    var modal = document.getElementById('report-modal');

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

    function pairExclusive(textInputId, fileInputId, nameId) {
        var textInput = document.getElementById(textInputId);
        var fileInput = document.getElementById(fileInputId);
        var nameLabel = document.getElementById(nameId);
        if (!textInput || !fileInput) return;

        textInput.addEventListener('input', function () {
            var hasText = textInput.value.trim() !== '';
            fileInput.disabled = hasText;
            if (hasText) {
                fileInput.value = '';
                if (nameLabel) nameLabel.textContent = 'Belum ada file dipilih';
            }
        });

        fileInput.addEventListener('change', function () {
            var hasFile = fileInput.files.length > 0;
            if (nameLabel) nameLabel.textContent = hasFile ? fileInput.files[0].name : 'Belum ada file dipilih';
            textInput.disabled = hasFile;
            if (hasFile) textInput.value = '';
        });
    }

    pairExclusive('hasil_link', 'hasil_file', 'hasil_file_name');
    pairExclusive('laporan_link', 'report_file', 'report_file_name');

    var xButton = document.getElementById('report-modal-x');
    if (xButton) xButton.addEventListener('click', closeModal);

    var cancelButton = document.getElementById('report-modal-cancel');
    if (cancelButton) cancelButton.addEventListener('click', closeModal);

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
