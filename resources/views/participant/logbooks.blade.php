@extends('layouts.app')
@section('title', 'Logbook')
@section('content')
<x-back-link />
<div class="mt-4">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Immersion · Rekam jejak</p>
    <h1 class="text-2xl font-semibold">Logbook</h1>
    <p class="mt-1 text-sm text-muted">Refleksi harian selama program magang dosen. Diisi oleh peserta, lalu diverifikasi mentor.</p>
</div>

@unless($program)
    <x-empty class="mt-6" title="Logbook terbuka setelah program aktif">
        Belum ada program magang dosen untuk Anda. Lengkapi profil lalu ajukan minat ke unit bisnis.
    </x-empty>
@else
    @php
        $monthStart = $month->copy()->startOfMonth();
        $monthTitle = $monthStart->copy()->locale('id')->translatedFormat('F Y');
        $daysInMonth = $monthStart->daysInMonth;
        $leadBlanks = $monthStart->isoWeekday() - 1;
        $totalCells = (int) ceil(($leadBlanks + $daysInMonth) / 7) * 7;
        $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $canFill = $program->status === 'active';
        $todayKey = today()->toDateString();
    @endphp
    <div class="mt-6 rounded-2xl border border-line bg-white p-5">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('participant.logbooks', ['month' => $monthStart->copy()->subMonth()->format('Y-m'), 'date' => $formDate]) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-line text-muted transition hover:border-primary hover:text-primary-dark" title="Bulan sebelumnya">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </a>
            <div class="text-center">
                <p class="font-semibold">{{ $monthTitle }}</p>
                <p class="text-xs text-muted">Klik tanggal untuk mengisi atau melihat logbook</p>
            </div>
            <a href="{{ route('participant.logbooks', ['month' => $monthStart->copy()->addMonth()->format('Y-m'), 'date' => $formDate]) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-line text-muted transition hover:border-primary hover:text-primary-dark" title="Bulan berikutnya">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </a>
        </div>
        <div class="mt-4 grid grid-cols-7 gap-1.5 text-center">
            @foreach($weekdays as $weekday)
                <p class="py-1 text-xs font-semibold text-muted">{{ $weekday }}</p>
            @endforeach
            @for($cell = 0; $cell < $totalCells; $cell++)
                @php
                    $dayNumber = $cell - $leadBlanks + 1;
                    $inMonth = $dayNumber >= 1 && $dayNumber <= $daysInMonth;
                    $cellKey = $inMonth ? $monthStart->copy()->addDays($dayNumber - 1)->toDateString() : null;
                    $dayLogs = $cellKey ? $byDate->get($cellKey, collect()) : collect();
                    $mark = $dayLogs->last();
                    $isToday = $cellKey === $todayKey;
                    $numberClass = $isToday ? 'font-bold text-primary-dark underline underline-offset-4' : 'font-medium';
                @endphp
                @if(! $inMonth)
                    <div class="rounded-lg bg-bg/50 px-1 py-1.5"></div>
                @else
                    <button type="button" data-date="{{ $cellKey }}" class="day-pick rounded-lg border px-1 py-1.5 transition hover:border-primary {{ $cellKey === $formDate ? 'border-primary bg-primary/10 ring-1 ring-primary' : 'border-line bg-white' }}">
                        <p class="text-sm {{ $numberClass }}">{{ $dayNumber }}</p>
                        <p class="mt-0.5 flex justify-center"><x-logbook-marker :status="$mark?->status" :attendance="$mark?->attendance" /></p>
                    </button>
                @endif
            @endfor
        </div>
        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-line pt-4 text-xs text-muted">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>Hadir disetujui</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-sky-600">schedule</span>Menunggu Tindakan Mentor</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-amber-600">warning</span>Perlu Tindakan Anda</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-indigo-500">event_busy</span>Izin</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-rose-500">sick</span>Sakit</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-slate-500">help</span>Tanpa Keterangan</span>
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-slate-300">remove</span>Belum Diisi</span>
        </div>
    </div>

    <div class="hidden">
        @foreach($byDate as $historyKey => $historyLogs)
            <div id="loghist-{{ $historyKey }}" class="space-y-3">
                @foreach($historyLogs as $log)
                    <x-logbook-entry :log="$log" />
                @endforeach
            </div>
        @endforeach
    </div>

    @if(! $canFill)
        <div class="mt-6 rounded-2xl border border-line bg-white p-6">
            <h2 class="font-semibold">Belum bisa diisi</h2>
            <p class="mt-2 text-sm text-muted">
                Logbook hanya terbuka saat program status <b>Aktif</b>. Status saat ini: <b>{{ \App\Support\Status::label($program->status) }}</b>.
                Selesaikan <b>Perjanjian</b> (ditandatangani dosen dan mentor) untuk mengaktifkan program.
            </p>
            <a href="{{ route('participant.agreement') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Selesaikan Perjanjian
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    @endif

    @if($canFill)
    <div id="logbook-modal" class="{{ $errors->any() ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-ink">Logbook Harian</h3>
                    <p id="logbook-modal-date" class="mt-1 text-xs text-muted">{{ \Carbon\Carbon::parse($formDate)->locale('id')->translatedFormat('d F Y') }}</p>
                </div>
                <button type="button" id="logbook-modal-x" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-line text-muted hover:text-ink">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            @if($errors->any())
                <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
            @endif
            <form method="POST" class="mt-5 space-y-4">
                @csrf
                <input id="entry_date" type="hidden" name="entry_date" value="{{ old('entry_date', $formDate) }}">
                <div>
                    <label class="text-xs font-medium text-muted">Kehadiran</label>
                    <select id="logbook_attendance" name="attendance" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
                        @foreach(\App\Models\Logbook::ATTENDANCE_TYPES as $attendanceType)
                            <option value="{{ $attendanceType }}" @selected(old('attendance', 'Hadir') === $attendanceType)>{{ $attendanceType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Apa yang dilakukan</label>
                    <textarea id="logbook_what_did" name="what_did" rows="3" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_did') }}</textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Apa yang dipelajari</label>
                    <textarea id="logbook_what_learned" name="what_learned" rows="3" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_learned') }}</textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Apa yang ditemukan</label>
                    <textarea id="logbook_what_found" name="what_found" rows="3" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_found') }}</textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Kendala</label>
                    <textarea id="logbook_obstacles" name="obstacles" rows="2" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('obstacles') }}</textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Output hari ini</label>
                    <textarea id="logbook_output" name="output" rows="2" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('output') }}</textarea>
                </div>
                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" id="logbook-modal-cancel" class="rounded-lg border border-line px-4 py-2.5 text-sm font-semibold text-muted">Batal</button>
                    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div id="logbook-history-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-ink">Riwayat Logbook</h3>
                    <p id="logbook-history-date" class="mt-1 text-xs text-muted"></p>
                </div>
                <button type="button" id="logbook-history-x" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-line text-muted hover:text-ink">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            <div id="logbook-history-body" class="mt-4 space-y-3"></div>
            <div class="mt-4 flex justify-end gap-3">
                @if($canFill)
                    <button type="button" id="logbook-history-refill" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Edit</button>
                @endif
                <button type="button" id="logbook-history-close" class="rounded-lg border border-line px-4 py-2.5 text-sm font-semibold text-muted">Tutup</button>
            </div>
        </div>
    </div>

    <script>
    var LOG_ENTRIES = @json($entryPrefill);
    (function () {
        var modal = document.getElementById('logbook-modal');
        var input = document.getElementById('entry_date');
        var dateLabel = document.getElementById('logbook-modal-date');
        var historyModal = document.getElementById('logbook-history-modal');
        var historyBody = document.getElementById('logbook-history-body');
        var historyDate = document.getElementById('logbook-history-date');
        var refillBtn = document.getElementById('logbook-history-refill');
        var buttons = document.querySelectorAll('.day-pick');
        var currentDate = null;

        function showModal(target) {
            if (!target) return;
            target.classList.remove('hidden');
            target.classList.add('flex');
        }

        function hideModal(target) {
            if (!target) return;
            target.classList.add('hidden');
            target.classList.remove('flex');
        }

        function formatDate(date) {
            try {
                return new Date(date + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            } catch (e) {
                return date;
            }
        }

        function setField(id, value) {
            var field = document.getElementById(id);
            if (field) field.value = value;
        }

        function openForm(date) {
            if (input && date) {
                input.value = date;
                if (dateLabel) dateLabel.textContent = formatDate(date);
            }
            var existing = (typeof LOG_ENTRIES !== 'undefined' && date && LOG_ENTRIES[date]) || null;
            setField('logbook_attendance', existing ? existing.attendance : 'Hadir');
            setField('logbook_what_did', existing ? existing.what_did : '');
            setField('logbook_what_learned', existing ? existing.what_learned : '');
            setField('logbook_what_found', existing ? existing.what_found : '');
            setField('logbook_obstacles', existing ? existing.obstacles : '');
            setField('logbook_output', existing ? existing.output : '');
            hideModal(historyModal);
            showModal(modal);
        }

        function openHistory(date) {
            var source = document.getElementById('loghist-' + date);
            if (!source) return;
            if (historyBody) historyBody.innerHTML = source.innerHTML;
            if (historyDate) historyDate.textContent = formatDate(date);
            if (refillBtn) refillBtn.dataset.date = date;
            hideModal(modal);
            showModal(historyModal);
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var date = button.dataset.date;
                currentDate = date;
                buttons.forEach(function (other) {
                    var selected = other === button;
                    other.classList.toggle('border-primary', selected);
                    other.classList.toggle('bg-primary/10', selected);
                    other.classList.toggle('ring-1', selected);
                    other.classList.toggle('ring-primary', selected);
                    other.classList.toggle('border-line', !selected);
                    other.classList.toggle('bg-white', !selected);
                });
                if (document.getElementById('loghist-' + date)) {
                    openHistory(date);
                } else {
                    openForm(date);
                }
            });
        });

        function wireClose(id, target) {
            var button = document.getElementById(id);
            if (button) button.addEventListener('click', function () { hideModal(target); });
        }

        wireClose('logbook-modal-x', modal);
        wireClose('logbook-modal-cancel', modal);
        wireClose('logbook-history-x', historyModal);
        wireClose('logbook-history-close', historyModal);

        if (refillBtn) refillBtn.addEventListener('click', function () {
            openForm(refillBtn.dataset.date || currentDate);
        });

        [modal, historyModal].forEach(function (target) {
            if (!target) return;
            var downTarget = null;
            target.addEventListener('mousedown', function (event) {
                downTarget = event.target;
            });
            target.addEventListener('click', function (event) {
                if (event.target === target && downTarget === target) hideModal(target);
                downTarget = null;
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            if (modal && !modal.classList.contains('hidden')) hideModal(modal);
            if (historyModal && !historyModal.classList.contains('hidden')) hideModal(historyModal);
        });
    })();
    </script>
@endunless
@endsection
