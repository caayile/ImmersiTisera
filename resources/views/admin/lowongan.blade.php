@extends('layouts.app')
@section('title', 'Lowongan')
@section('content')
@php
    $hasOpenAllError = $errors->has('batch') || $errors->has('registration_start') || $errors->has('registration_deadline');
@endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Lowongan magang dosen</h1>
        <p class="mt-1 text-sm text-muted">Kelola daftar lowongan yang sudah tersedia di tiap unit bisnis serta buka atau tutup pendaftarannya.</p>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-line bg-white px-4 py-2.5">
        <div>
            <p class="text-xs font-semibold text-muted">Semua lowongan</p>
            <p class="text-[10px] text-muted">Buka / tutup semua</p>
        </div>
        <form method="POST" action="{{ route('admin.lowongan.toggle-all') }}" id="bulk-close-form" class="hidden">
            @csrf
            <input type="hidden" name="status" value="closed">
        </form>
        <label class="relative inline-flex cursor-pointer items-center">
            <input type="checkbox" id="bulk-toggle" class="peer sr-only" @checked($allOpen || $hasOpenAllError)>
            <span class="relative inline-block h-6 w-11 rounded-full transition {{ $allOpen ? 'bg-primary' : 'bg-slate-300' }} peer-checked:bg-primary"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
        </label>
    </div>
</div>

{{-- Modal: buka semua (set batch & periode) --}}
<div id="bulk-open-modal" class="{{ $hasOpenAllError ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-ink">Buka semua lowongan</h3>
                <p class="mt-1 text-xs text-muted">Tentukan batch pembukaan beserta periode pendaftarannya.</p>
            </div>
            <button type="button" id="bulk-modal-x" class="flex h-8 w-8 items-center justify-center rounded-lg border border-line text-muted hover:text-ink">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.lowongan.open-all') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="text-xs font-medium text-muted">Batch pembukaan</label>
                <input name="batch" value="{{ old('batch', $batchDefault) }}" placeholder="Nama batch" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-muted">Dibuka pada</label>
                    <input type="date" name="registration_start" value="{{ old('registration_start') }}" required class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Ditutup pada</label>
                    <input type="date" name="registration_deadline" value="{{ old('registration_deadline') }}" required class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                </div>
            </div>
            <p class="text-xs leading-snug text-muted">Tanggal mulai berlaku pukul 00.00, tanggal tutup sampai pukul 23.59.</p>
            @if($errors->has('batch') || $errors->has('registration_start') || $errors->has('registration_deadline'))
                <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</p>
            @endif
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" id="bulk-modal-cancel" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold text-muted">Batal</button>
                <button class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">lock_open</span>
                    Buka semua
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var toggle = document.getElementById('bulk-toggle');
    var modal = document.getElementById('bulk-open-modal');
    var closeForm = document.getElementById('bulk-close-form');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (toggle) toggle.checked = false;
    }

    if (toggle && closeForm) {
        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                openModal();
            } else {
                closeForm.submit();
            }
        });
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });
    }

    var xButton = document.getElementById('bulk-modal-x');
    if (xButton) xButton.addEventListener('click', closeModal);

    var cancelButton = document.getElementById('bulk-modal-cancel');
    if (cancelButton) cancelButton.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });
})();</script>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($units as $unit)
        @php
            $open = $unit->status === 'open';
            $scheduled = $unit->registration_start !== null && $unit->registration_deadline !== null;
            $quota = \App\Models\BusinessUnit::MAX_APPLICANTS;
            $filled = (int) ($unit->active_applications_count ?? $unit->applications_count ?? 0);
            $isFull = $filled >= $quota;
        @endphp
        <article class="flex flex-col rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
            {{-- 1. Header Card --}}
            <div class="flex items-start justify-between gap-2">
                <span class="inline-flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full bg-secondary/30 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-ink">{{ $unit->department->name }}</span>
                    @if($unit->department->area)
                        <span class="rounded-full bg-bg px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-muted">{{ $unit->department->area }}</span>
                    @endif
                </span>
                @if($isFull)
                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-red-700">Penuh {{ $filled }}/{{ $quota }}</span>
                @elseif($open)
                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-sky-700">Buka · {{ $filled }}/{{ $quota }}</span>
                @else
                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-red-700">Ditutup · {{ $filled }}/{{ $quota }}</span>
                @endif
            </div>

            <h3 class="mt-3 text-lg font-bold leading-tight text-ink">{{ $unit->name }}</h3>
            <p class="mt-1 text-xs text-muted">
                @if($unit->registration_start && $unit->registration_deadline)
                    Dibuka {{ $unit->registration_start->format('d M Y') }} · ditutup {{ $unit->registration_deadline->format('d M Y') }} (23.59)
                @elseif($unit->registration_deadline)
                    Pendaftaran s/d {{ $unit->registration_deadline->format('d M Y') }} (23.59)
                @else
                    Belum dijadwalkan pendaftaran
                @endif
            </p>

            {{-- 2. Tombol aksi & navigasi cepat --}}
            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('units.show', $unit) }}" title="Detail" class="inline-flex items-center gap-1.5 rounded-lg bg-primary/12 px-3 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-[18px]">info</span>
                    Detail
                </a>
                <a href="{{ route('admin.matching', ['unit' => $unit->id]) }}" title="Lihat pendaftar" class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-sm font-semibold text-ink transition hover:border-primary hover:text-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                    Pendaftar
                    <span class="rounded-full bg-bg px-1.5 text-[11px] font-bold text-muted">{{ $unit->applications_count }}</span>
                </a>
                <div class="ml-auto flex shrink-0 items-center gap-1.5">
                    <a href="{{ route('admin.units.edit', $unit) }}" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-primary hover:text-primary-dark">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </a>
                    <button type="button" title="Hapus" onclick="if (confirm('Hapus lowongan ini?')) document.getElementById('delete-lowongan-{{ $unit->id }}').submit()" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-red-300 hover:text-red-600">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </div>

            {{-- 3. Periode pendaftaran --}}
            <form method="POST" action="{{ route('admin.lowongan.period', $unit) }}" class="mt-4 rounded-xl border border-line bg-bg/60 p-3">
                @csrf
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-ink">Periode pendaftaran</p>
                    <div class="flex items-center gap-2">
                        @if($unit->batch)
                            <span class="rounded-full bg-primary/12 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-primary-dark">{{ $unit->batch }}</span>
                        @endif
                        <button class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            Simpan
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="text-[11px] font-medium text-muted">Batch (ganti nama untuk mereset kuota 2 pendaftar)</label>
                    <input type="text" name="batch" value="{{ old('batch', $unit->batch) }}" placeholder="cth. Batch Oktober 2026" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5 text-xs">
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[11px] font-medium text-muted">Dibuka pada</label>
                        <input type="date" name="registration_start" value="{{ $unit->registration_start?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-muted">Ditutup pada</label>
                        <input type="date" name="registration_deadline" value="{{ $unit->registration_deadline?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5 text-xs">
                    </div>
                </div>
                <p class="mt-2 text-[11px] leading-snug text-muted">Tanggal mulai berlaku pukul 00.00, tanggal tutup sampai pukul 23.59.</p>
            </form>

            {{-- 4. Toggle status lowongan --}}
            <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-line bg-bg/60 p-3">
                <div>
                    <p class="text-sm font-semibold text-ink">Status lowongan</p>
                    <p class="mt-0.5 text-[11px] leading-snug text-muted">
                        @if($scheduled)
                            {{ $open ? 'Lowongan sedang terbuka.' : 'Lowongan sedang ditutup.' }}
                        @else
                            Atur periode pendaftaran terlebih dahulu untuk mengoperasikan toggle.
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.lowongan.toggle', $unit) }}" class="shrink-0 {{ $scheduled ? '' : 'pointer-events-none opacity-40' }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $open ? 'closed' : 'open' }}">
                    <label class="relative inline-flex cursor-pointer items-center" title="{{ $scheduled ? ($open ? 'Lowongan terbuka' : 'Lowongan ditutup') : 'Periode pendaftaran belum diatur' }}">
                        <input type="checkbox" class="peer sr-only" @checked($open) @disabled(! $scheduled) onchange="this.form.submit()">
                        <span class="relative inline-block h-6 w-11 rounded-full transition {{ $open ? 'bg-primary' : 'bg-slate-300' }} peer-checked:bg-primary"></span>
                        <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </label>
                </form>
            </div>
        </article>
    @empty
        <div class="sm:col-span-2 xl:col-span-3">
            <x-empty title="Belum ada lowongan" />
        </div>
    @endforelse
</div>

@foreach($units as $unit)
    <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" id="delete-lowongan-{{ $unit->id }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach
@endsection