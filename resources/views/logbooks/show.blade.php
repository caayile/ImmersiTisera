@extends('layouts.app')
@section('title', 'Detail Logbook')
@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <a href="{{ route($backRoute) }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke daftar logbook</a>
        <h1 class="mt-3 text-2xl font-semibold">Logbook {{ $program->participant->user->name }}</h1>
        <p class="mt-1 text-sm text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p>
    </div>
    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary-dark">{{ $program->logbooks->count() }} entri</span>
</div>

@php
    $monthStart = $month->copy()->startOfMonth();
    $monthTitle = $monthStart->copy()->locale('id')->translatedFormat('F Y');
    $daysInMonth = $monthStart->daysInMonth;
    $leadBlanks = $monthStart->isoWeekday() - 1;
    $totalCells = (int) ceil(($leadBlanks + $daysInMonth) / 7) * 7;
    $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    $todayKey = today()->toDateString();
@endphp
<div class="mt-6 rounded-2xl border border-line bg-white p-5">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['month' => $monthStart->copy()->subMonth()->format('Y-m')])) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-line text-muted transition hover:border-primary hover:text-primary-dark" title="Bulan sebelumnya">
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
        </a>
        <div class="text-center">
            <p class="font-semibold">{{ $monthTitle }}</p>
            <p class="text-xs text-muted">Klik tanggal untuk melihat rincian</p>
        </div>
        <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['month' => $monthStart->copy()->addMonth()->format('Y-m')])) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-line text-muted transition hover:border-primary hover:text-primary-dark" title="Bulan berikutnya">
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
                <button type="button" data-date="{{ $cellKey }}" class="day-pick rounded-lg border border-line bg-white px-1 py-1.5 transition hover:border-primary">
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
                @if($canReview)
                    <form method="POST" action="{{ route('mentor.logbooks.review', $log) }}" class="grid gap-2 rounded-xl border border-line bg-bg/60 p-3 md:grid-cols-[1fr_180px_auto]">
                        @csrf
                        <input name="mentor_feedback" value="{{ $log->mentor_feedback }}" placeholder="Tulis feedback / langkah berikutnya" class="rounded-lg border border-line px-3 py-2 text-sm">
                        <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <option value="reviewed" @selected($log->status === 'reviewed')>Ditinjau</option>
                            <option value="revision" @selected($log->status === 'revision')>Minta revisi</option>
                            <option value="approved" @selected($log->status === 'approved')>Disetujui</option>
                        </select>
                        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                    </form>
                @elseif($log->mentor_feedback)
                    <div class="rounded-xl border border-line bg-bg/60 p-3 text-sm"><p class="font-semibold">Feedback mentor</p><p class="mt-1">{{ $log->mentor_feedback }}</p></div>
                @endif
            @endforeach
        </div>
    @endforeach
</div>

<div id="logbook-history-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-ink">Rincian Logbook</h3>
                <p id="logbook-history-date" class="mt-1 text-xs text-muted"></p>
            </div>
            <button type="button" id="logbook-history-x" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-line text-muted hover:text-ink">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div id="logbook-history-body" class="mt-4 space-y-3"></div>
        <div class="mt-4 flex justify-end">
            <button type="button" id="logbook-history-close" class="rounded-lg border border-line px-4 py-2.5 text-sm font-semibold text-muted">Tutup</button>
        </div>
    </div>
</div>

<script>
(function () {
    var historyModal = document.getElementById('logbook-history-modal');
    var historyBody = document.getElementById('logbook-history-body');
    var historyDate = document.getElementById('logbook-history-date');
    var buttons = document.querySelectorAll('.day-pick');

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

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var date = button.dataset.date;
            var source = document.getElementById('loghist-' + date);
            if (!source || !historyBody) return;
            historyBody.innerHTML = source.innerHTML;
            if (historyDate) historyDate.textContent = formatDate(date);
            showModal(historyModal);
        });
    });

    function wireClose(id, target) {
        var button = document.getElementById(id);
        if (button) button.addEventListener('click', function () { hideModal(target); });
    }

    wireClose('logbook-history-x', historyModal);
    wireClose('logbook-history-close', historyModal);

    if (historyModal) {
        var downTarget = null;
        historyModal.addEventListener('mousedown', function (event) {
            downTarget = event.target;
        });
        historyModal.addEventListener('click', function (event) {
            if (event.target === historyModal && downTarget === historyModal) hideModal(historyModal);
            downTarget = null;
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && historyModal && !historyModal.classList.contains('hidden')) hideModal(historyModal);
    });
})();
</script>
@endsection
