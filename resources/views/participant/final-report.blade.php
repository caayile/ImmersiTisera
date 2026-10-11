@extends('layouts.app')
@section('title', 'Laporan Akhir Masa Magang')
@section('content')
@php
    $programReport = $program?->outputs?->firstWhere('is_final_report', true);
    $reportYearFallback = $program?->end_date?->format('Y') ?? $program?->start_date?->format('Y') ?? now()->format('Y');
@endphp
<div x-data="reportForm()">
<div class="flex flex-wrap items-start justify-between gap-3">
    <p class="text-sm text-muted">Laporan akhir diisi di akhir masa program.</p>
    @if($program && ! $programReport)
        <button type="button" @click="openReport('{{ $program->id }}')" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#22b07d] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a9a6d]">
            <span class="text-lg leading-none">+</span>
            Buat Laporan
        </button>
    @endif
</div>

@unless($program)
    <x-empty class="mt-6 w-full" title="Laporan akhir belum tersedia" />
@else
    {{-- Popup Form Laporan Akhir --}}
    <div x-show="reportOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="reportOpen && 'flex'" @click.self="reportOpen = false" @keydown.escape.window="reportOpen = false" role="dialog" aria-modal="true" aria-label="Form laporan akhir">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Form Laporan Akhir</h2>
                    <p class="mt-0.5 text-sm text-muted">Lengkapi metadata Laporan Akhir</p>
                </div>
                <button type="button" @click="reportOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
            </div>
            @if($errors->any())
                <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
            @endif
            <form method="POST" action="{{ route('participant.final-report') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="is_final_report" value="1">
                <input type="hidden" name="type" value="Research Report">
                <input type="hidden" name="program_id" :value="reportCycle">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">Unit Bisnis</label>
                        <select name="department_id" x-ref="dept" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm">
                            <option value="">Pilih Unit Bisnis</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((int) old('department_id', $programReport?->department_id ?? $program->department_id) === (int) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold">Departement</label>
                        <select name="business_unit_id" x-ref="unit" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm">
                            <option value="">Pilih Departement</option>
                            @foreach($businessUnits->groupBy(fn ($unit) => $unit->department?->name ?: 'Lainnya') as $groupName => $groupUnits)
                                <optgroup label="{{ $groupName }}">
                                    @foreach($groupUnits as $unit)
                                        <option value="{{ $unit->id }}" @selected((int) old('business_unit_id', $programReport?->business_unit_id ?? $program->business_unit_id) === (int) $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="report-title" class="text-sm font-semibold">Judul Laporan</label>
                    <textarea id="report-title" name="title" x-ref="title" rows="3" placeholder="Masukkan judul laporan" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400" required>{{ old('title', $programReport?->title) }}</textarea>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">Tahun</label>
                        <input name="year" x-ref="year" inputmode="numeric" maxlength="4" placeholder="cth. 2026" value="{{ old('year', $programReport?->year ?? $reportYearFallback) }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-semibold">Level Kolaborasi</label>
                        <select name="level" x-ref="level" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm">
                            <option value="">Pilih Level</option>
                            @for($level = 0; $level <= 4; $level++)
                                <option value="{{ $level }}" @selected((string) old('level', $program?->collaboration?->level) === (string) $level)>Level {{ $level }} — {{ \App\Support\Status::collaborationLevelLabel($level) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-semibold">Hasil Akhir</p>
                    <p class="mt-0.5 text-xs text-muted">Pilih Salah Satu teknik pengumpulan</p>
                    <input id="hasil_link" name="link" x-ref="link" placeholder="Link Hasil Laporan Akhir" value="{{ old('link', $programReport?->link) }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">
                    <div class="mt-3">
                        <input id="hasil_file" type="file" name="hasil_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.zip" class="sr-only">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-2.5">
                            <span id="hasil_file_name" x-ref="hasilNote" class="truncate text-sm text-muted">{{ $programReport?->hasil_file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Pilih File Dokumen' }}</span>
                            <label for="hasil_file" class="shrink-0 cursor-pointer rounded-lg bg-mint-light px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/15">Pilih File</label>
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-muted">Maksimal 10 MB.</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-semibold">Laporan Akhir</p>
                    <p class="mt-0.5 text-xs text-muted">Pilih Salah Satu teknik pengumpulan</p>
                    <input id="laporan_link" name="laporan_link" x-ref="lapLink" placeholder="Link Hasil Laporan Akhir" value="{{ old('laporan_link', $programReport?->laporan_link) }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">
                    <div class="mt-3">
                        <input id="report_file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.zip" class="sr-only">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-2.5">
                            <span id="report_file_name" x-ref="lapNote" class="truncate text-sm text-muted">{{ $programReport?->file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Pilih File Dokumen' }}</span>
                            <label for="report_file" class="shrink-0 cursor-pointer rounded-lg bg-mint-light px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/15">Pilih File</label>
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-muted">Maksimal 10 MB.</p>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="reportOpen = false" class="rounded-xl border border-primary/40 px-5 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">Batal</button>
                    <button x-text="(reports[reportCycle] && reports[reportCycle].exists) ? 'Update' : 'Kirim'" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">{{ $programReport ? 'Update' : 'Kirim' }}</button>
                </div>
            </form>
        </div>
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
    <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white shadow-sm">
        <table class="w-full min-w-[1020px] text-left text-sm">
            <thead>
                <tr class="border-b border-line text-muted">
                    <th class="px-4 py-3 font-medium">No</th>
                    <th class="px-4 py-3 font-medium">Unit Bisnis</th>
                    <th class="px-4 py-3 font-medium">Departemen</th>
                    <th class="px-4 py-3 font-medium">Judul Laporan</th>
                    <th class="px-4 py-3 font-medium">Tahun</th>
                    <th class="px-4 py-3 font-medium">Hasil</th>
                    <th class="px-4 py-3 font-medium">Laporan</th>
                    <th class="px-4 py-3 font-medium">Level</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody id="report-rows">
                @foreach($programs as $index => $cycle)
                    @php
                        $cycleReport = $cycle->outputs->firstWhere('is_final_report', true);
                        $rowUnitBisnis = $cycleReport?->department?->name ?? $cycle->department?->name;
                        $rowDepartement = $cycleReport?->businessUnit?->name ?? $cycle->businessUnit?->name;
                        $cycleYear = $cycleReport?->year ?? $cycle->end_date?->format('Y') ?? $cycle->start_date?->format('Y') ?? $cycleReport?->created_at?->format('Y') ?? '';
                        $hasilUrl = $cycleReport?->linkUrl() ?? $cycleReport?->hasilFileUrl();
                        $laporanUrl = $cycleReport?->laporanFileUrl() ?? $cycleReport?->laporanLinkUrl();
                        $isCurrentCycle = $cycle->id === $program->id;
                    @endphp
                    <tr class="report-row border-b border-line last:border-0"
                        data-search="{{ strtolower(($cycleReport?->title ?: '').' '.($rowUnitBisnis ?: '').' '.($rowDepartement ?: '')) }}"
                        data-year="{{ $cycleYear }}">
                        <td class="row-no px-4 py-3 align-top">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 align-top font-medium">{{ $rowUnitBisnis ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">{{ $rowDepartement ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">
                            <span class="font-medium">{{ $cycleReport?->title ?: 'Laporan Akhir — '.($cycle->businessUnit?->name ?: 'Unit Bisnis') }}</span>
                            <span class="mt-0.5 block text-xs text-muted">
                                @if($cycle->start_date || $cycle->end_date)
                                    {{ $cycle->start_date?->format('M Y') ?: '—' }} – {{ $cycle->end_date?->format('M Y') ?: '—' }}
                                @endif
                                @if($isCurrentCycle)
                                    · <span class="font-semibold text-primary-dark">Siklus berjalan</span>
                                @elseif(! $cycleReport)
                                    · <span>Belum ada laporan</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top">{{ $cycleYear ?: '—' }}</td>
                        <td class="px-4 py-3 align-top">
                            @if($hasilUrl)
                                <a href="{{ $hasilUrl }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-[#22b07d] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Hasil</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($laporanUrl)
                                <a href="{{ $laporanUrl }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-[#22b07d] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#1a9a6d]">Lihat Laporan</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($cycle->collaboration)
                                <span class="inline-flex whitespace-nowrap rounded-full border border-primary/40 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">Level {{ $cycle->collaboration->level }} · {{ \App\Support\Status::collaborationLevelLabel($cycle->collaboration->level) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <button type="button" @click="openReport('{{ $cycle->id }}')" title="Isi atau ubah laporan" aria-label="Isi atau ubah laporan" class="flex h-8 w-8 items-center justify-center rounded-lg border border-primary/40 text-primary-dark transition hover:bg-primary/5">
                                <span class="material-symbols-outlined text-[18px]">edit_square</span>
                            </button>
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($cycleReport)
                                <x-badge :status="$cycleReport->status" :label="$cycleReport->status === 'approved' ? 'Diterima Mentor' : \App\Support\Status::outputLabel($cycleReport->status)" />
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
function reportForm() {
    return {
        reportOpen: {{ $errors->any() ? 'true' : 'false' }},
        reportCycle: @json((string) old('program_id', $program->id)),
        reports: @json($reportFormData ?? []),
        openReport(id) {
            this.reportCycle = String(id);
            var d = this.reports[this.reportCycle] || {};
            this.$refs.title.value = d.title || '';
            this.$refs.year.value = d.year || '';
            this.$refs.dept.value = d.dept || '';
            this.$refs.unit.value = d.unit || '';
            this.$refs.level.value = d.level || '';
            this.$refs.link.value = d.link || '';
            this.$refs.lapLink.value = d.lapLink || '';
            var hasilFile = document.getElementById('hasil_file');
            var lapFile = document.getElementById('report_file');
            if (hasilFile) {
                hasilFile.value = '';
                hasilFile.disabled = (d.link || '').trim() !== '';
            }
            if (lapFile) {
                lapFile.value = '';
                lapFile.disabled = (d.lapLink || '').trim() !== '';
            }
            this.$refs.hasilNote.textContent = d.hasilNote || 'Pilih File Dokumen';
            this.$refs.lapNote.textContent = d.lapNote || 'Pilih File Dokumen';
            this.reportOpen = true;
        },
    };
}
(function () {
    var search = document.getElementById('report-search');
    var year = document.getElementById('report-year');
    var emptyMsg = document.getElementById('report-empty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.report-row'));
    if (search && rows.length) {
        var render = function () {
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
        };
        search.addEventListener('input', render);
        year.addEventListener('change', render);
    }

    function pairExclusive(textInputId, fileInputId, nameId, existingText) {
        var textInput = document.getElementById(textInputId);
        var fileInput = document.getElementById(fileInputId);
        var nameLabel = document.getElementById(nameId);
        if (!textInput || !fileInput) return;

        textInput.addEventListener('input', function () {
            var hasText = textInput.value.trim() !== '';
            fileInput.disabled = hasText;
            fileInput.classList.toggle('opacity-50', hasText);
            if (hasText) {
                fileInput.value = '';
                if (nameLabel) nameLabel.textContent = existingText || 'Belum ada file dipilih';
            }
        });

        fileInput.addEventListener('change', function () {
            var hasFile = fileInput.files.length > 0;
            if (nameLabel) nameLabel.textContent = hasFile ? fileInput.files[0].name : (existingText || 'Belum ada file dipilih');
            textInput.disabled = hasFile;
            if (hasFile) textInput.value = '';
        });

        if (textInput.value.trim() !== '') {
            fileInput.disabled = true;
        }
    }

    pairExclusive('hasil_link', 'hasil_file', 'hasil_file_name', @json($programReport?->hasil_file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Belum ada file dipilih'));
    pairExclusive('laporan_link', 'report_file', 'report_file_name', @json($programReport?->file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Belum ada file dipilih'));
})();
</script>
@endunless
</div>
@endsection
