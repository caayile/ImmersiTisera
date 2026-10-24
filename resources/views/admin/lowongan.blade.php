@extends('layouts.app')
@section('title', 'Lowongan magang dosen')
@section('content')
@php
    $hasOpenAllError = $errors->has('batch') || $errors->has('registration_start') || $errors->has('registration_deadline');
@endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-muted">Kelola daftar lowongan yang sudah tersedia di tiap unit bisnis serta buka atau tutup pendaftarannya.</p>
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

<div class="mt-6 space-y-8">
    @php
        $unitsByDept = $units->groupBy('department.id')->map(fn ($units) => $units->first()?->department ? [
            'department' => $units->first()->department,
            'units' => $units,
        ] : null)->filter();
    @endphp
    
    @forelse($unitsByDept as $group)
        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            {{-- Department header + open all button --}}
            <div class="flex items-center justify-between gap-3 border-b border-line pb-4">
                <div>
                    <h2 class="text-lg font-bold text-ink">{{ $group['department']->name }}</h2>
                    @if($group['department']->area)
                        <p class="mt-0.5 text-xs text-muted">{{ $group['department']->area }}</p>
                    @endif
                </div>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]" onclick="document.getElementById('open-dept-modal-{{ $group['department']->id }}').classList.remove('hidden')">
                    <span class="material-symbols-outlined text-[18px]">lock_open</span>
                    Buka semua
                </button>
            </div>

            {{-- Units grid --}}
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($group['units'] as $unit)
                    @php
                        $manualOpen = $unit->status === 'open';
                        $open = $unit->isOpen();
                        $scheduled = $unit->registration_start !== null && $unit->registration_deadline !== null;
                        $expired = $manualOpen && $scheduled && $unit->registration_deadline && now()->gt($unit->registration_deadline);
                        $notStarted = $manualOpen && $scheduled && $unit->registration_start && now()->lt($unit->registration_start);
                        $quota = \App\Models\BusinessUnit::MAX_APPLICANTS;
                        $filled = (int) ($unit->active_applications_count ?? $unit->applications_count ?? 0);
                        $isFull = $filled >= $quota;
                    @endphp
                    <article class="flex flex-col rounded-xl border border-line bg-bg/40 p-4">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-ink">{{ $unit->name }}</h3>
                                <p class="mt-0.5 text-xs text-muted">
                                    @if($unit->registration_start && $unit->registration_deadline)
                                        {{ $unit->registration_start->format('d M') }} – {{ $unit->registration_deadline->format('d M Y') }}
                                    @else
                                        Belum dijadwalkan
                                    @endif
                                </p>
                            </div>
                            @if($isFull)
                                <span class="shrink-0 rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700">{{ $filled }}/{{ $quota }}</span>
                            @elseif($open)
                                <span class="shrink-0 rounded-full bg-sky-50 px-2 py-1 text-[10px] font-semibold text-sky-700">Buka</span>
                            @else
                                <span class="shrink-0 rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700">Tutup</span>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-3 flex items-center gap-1.5">
                            <a href="{{ route('admin.units.edit', $unit) }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-line text-muted transition hover:border-primary hover:text-primary-dark">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </a>
                            <a href="{{ route('admin.matching', ['unit' => $unit->id]) }}" class="flex-1 rounded-lg border border-line px-3 py-1.5 text-xs font-semibold text-ink transition hover:border-primary hover:text-primary-dark">
                                Pendaftar ({{ $unit->applications_count }})
                            </a>
                            <button type="button" onclick="if (confirm('Hapus lowongan ini?')) document.getElementById('delete-lowongan-{{ $unit->id }}').submit()" class="flex h-8 w-8 items-center justify-center rounded-lg border border-line text-muted transition hover:border-red-300 hover:text-red-600">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>

                        {{-- Period form --}}
                        <form method="POST" action="{{ route('admin.lowongan.period', $unit) }}" class="mt-3 space-y-2 rounded-lg border border-line bg-white p-3">
                            @csrf
                            <div>
                                <label class="text-[10px] font-medium text-muted">Batch</label>
                                <input type="text" name="batch" value="{{ old('batch', $unit->batch) }}" placeholder="Batch Oktober 2026" class="mt-0.5 w-full rounded-lg border border-line px-2 py-1 text-xs">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-medium text-muted">Dibuka</label>
                                    <input type="date" name="registration_start" value="{{ $unit->registration_start?->format('Y-m-d') }}" class="mt-0.5 w-full rounded-lg border border-line px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="text-[10px] font-medium text-muted">Ditutup</label>
                                    <input type="date" name="registration_deadline" value="{{ $unit->registration_deadline?->format('Y-m-d') }}" class="mt-0.5 w-full rounded-lg border border-line px-2 py-1 text-xs">
                                </div>
                            </div>
                            <button class="w-full rounded-lg bg-primary px-2 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-dark">Simpan</button>
                        </form>

                        {{-- Toggle status --}}
                        <form method="POST" action="{{ route('admin.lowongan.toggle', $unit) }}" class="mt-3 flex items-center gap-2 rounded-lg border border-line bg-white p-3 {{ $scheduled ? '' : 'opacity-50' }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $manualOpen ? 'closed' : 'open' }}">
                            <label class="relative inline-flex cursor-pointer items-center flex-1">
                                <input type="checkbox" class="peer sr-only" @checked($manualOpen) @disabled(! $scheduled) onchange="this.form.submit()">
                                <span class="relative inline-block h-5 w-9 rounded-full transition {{ $manualOpen ? 'bg-primary' : 'bg-slate-300' }} peer-checked:bg-primary"></span>
                                <span class="pointer-events-none absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-4"></span>
                            </label>
                            <span class="text-xs text-muted">{{ $manualOpen ? 'Buka' : 'Tutup' }}</span>
                        </form>
                    </article>
                @endforeach
            </div>

            {{-- Modal: buka semua unit di department ini --}}
            <div id="open-dept-modal-{{ $group['department']->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-ink">Buka semua lowongan di {{ $group['department']->name }}</h3>
                    <p class="mt-1 text-xs text-muted">Tentukan batch pembukaan beserta periode pendaftarannya untuk semua unit bisnis di departemen ini.</p>
                    <form method="POST" action="{{ route('admin.lowongan.open-department', $group['department']) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs font-medium text-muted">Batch pembukaan</label>
                            <input name="batch" value="{{ old('batch', \App\Http\Controllers\AdminController::batchDefault()) }}" placeholder="Nama batch" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-muted">Dibuka pada</label>
                                <input type="date" name="registration_start" required class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-muted">Ditutup pada</label>
                                <input type="date" name="registration_deadline" required class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                            </div>
                        </div>
                        <p class="text-xs leading-snug text-muted">Tanggal mulai berlaku pukul 00.00, tanggal tutup sampai pukul 23.59.</p>
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" onclick="document.getElementById('open-dept-modal-{{ $group['department']->id }}').classList.add('hidden')" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold text-muted">Batal</button>
                            <button class="inline-flex items-center gap-1.5 rounded-lg bg-[#22b07d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">
                                <span class="material-symbols-outlined text-[18px]">lock_open</span>
                                Buka semua
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    @empty
        <div>
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