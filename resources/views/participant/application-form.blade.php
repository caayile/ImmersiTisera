@extends('layouts.form')
@section('title', $application ? ((isset($readOnly) && $readOnly) ? 'Data Pendaftaran' : 'Perbaiki Pendaftaran') : 'Daftar Program')
@section('content')
@php
    $user = auth()->user();
    $readOnly = $readOnly ?? false;
    $canSubmit = ! $application || ! $readOnly;
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $disabled = $readOnly ? 'disabled' : '';
@endphp

<div x-data="{
    step: {{ old('shared_goal') || old('declaration') || old('participant_signature') ? 2 : 1 }},
    periodStart: @js(old('period_start', $periodStart)),
    periodEnd: @js(old('period_end', $periodEnd)),
    addMonths(value, months) {
        if (! value) return '';
        const parts = value.split('-').map(Number);
        const date = new Date(Date.UTC(parts[0], parts[1] - 1 + months, 1));
        const last = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 0)).getUTCDate();
        date.setUTCDate(Math.min(parts[2], last));
        return date.toISOString().slice(0, 10);
    },
    onStartChange() {
        if (this.periodStart) this.periodEnd = this.addMonths(this.periodStart, 2);
    },
    onEndChange() {
        if (this.periodEnd) this.periodStart = this.addMonths(this.periodEnd, -2);
    },
    formatPeriod() {
        if (! this.periodStart || ! this.periodEnd) return '—';
        const fmt = (value) => {
            const [y, m, d] = value.split('-').map(Number);
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            return String(d).padStart(2, '0') + ' ' + months[m - 1] + ' ' + y;
        };
        return fmt(this.periodStart) + ' – ' + fmt(this.periodEnd);
    },
    next() {
        const form = document.getElementById('application-form');
        if (! form) { this.step = 2; return; }
        const start = form.elements['period_start'];
        const end = form.elements['period_end'];
        if (! start.value) { start.reportValidity(); return; }
        if (! end.value) { end.reportValidity(); return; }
        this.step = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        this.$nextTick(() => window.dispatchEvent(new Event('resize')));
    },
    back() {
        this.step = 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}">
    <a href="{{ $application ? route('participant.applications.show', $application) : ($unit ? route('units.show', $unit) : route('departments.index')) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Kembali
    </a>

    <section class="mt-5 overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="hero-grid px-6 py-8 md:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Pendaftaran dosen</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white md:text-4xl">
                @if($readOnly)
                    Data pendaftaran (hanya lihat)
                @elseif($application)
                    Perbaiki form pendaftaran
                @else
                    Isi Formulir Registrasi
                @endif
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-white/75">
                @if($readOnly)
                    Pendaftaran sedang diproses. Anda dapat melihat data yang sudah dikirim, tetapi tidak dapat mengirim ulang kecuali diminta revisi.
                @elseif($application)
                    Sesuaikan pernyataan sesuai catatan mentor/admin, lalu kirim ulang. Jika revisi dari mentor, surat langsung kembali ke mentor untuk ditinjau dan ditandatangani.
                @else
                    Lengkapi data diri dan informasi akademik Anda untuk mengikuti program Magang Dosen TSU.
                @endif
            </p>
        </div>
    </section>

    <section class="mt-5 rounded-3xl border border-line bg-white p-6 shadow-sm md:p-8">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">route</span>
            </span>
            <div>
                <h2 class="text-lg font-semibold">Tahapan pendaftaran</h2>
                <p class="mt-1 text-sm text-muted">
                    {{ $application ? $application->currentStageLabel() : 'Selesaikan 2 langkah berikut sebelum dikirim ke admin.' }}
                </p>
            </div>
        </div>
        <div class="mt-5">
            @if($application)
                <x-approval-flow :application="$application" />
            @else
                <ol class="flex flex-col gap-5 sm:grid sm:grid-cols-2 sm:gap-6">
                    <li @click="back()" title="Kembali ke Langkah 1" class="relative flex cursor-pointer gap-3 sm:flex-col sm:items-center sm:px-1 sm:text-center" :aria-current="step === 1 ? 'step' : 'false'">
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 transition" :class="step === 1 ? 'border-primary bg-primary text-white ring-4 ring-primary/20' : 'border-emerald-500 bg-emerald-500 text-white'">
                            <span x-show="step === 1" class="text-sm font-bold">1</span>
                            <span x-show="step !== 1" class="material-symbols-outlined text-[18px]" style="display: none;">check</span>
                            <span x-show="step === 1" class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 animate-pulse rounded-full bg-primary ring-2 ring-white"></span>
                        </div>
                        <div class="min-w-0 sm:mt-2">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" :class="step === 1 ? 'text-primary-dark' : 'text-emerald-600'">1. Langkah 1</p>
                            <p class="mt-0.5 text-sm font-semibold leading-snug text-ink">Isi Formulir Registrasi</p>
                            <span x-show="step === 1" class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-primary"></span>
                                Sedang mengisi
                            </span>
                            <span x-show="step !== 1" class="mt-1.5 inline-block text-[11px] font-medium text-emerald-600" style="display: none;">Selesai</span>
                        </div>
                    </li>
                    <li @click="next()" title="Lanjut ke Langkah 2" class="relative flex cursor-pointer gap-3 sm:flex-col sm:items-center sm:px-1 sm:text-center" :aria-current="step === 2 ? 'step' : 'false'">
                        <span aria-hidden="true" class="absolute left-5 top-10 h-[calc(100%-2.75rem)] w-px -translate-x-1/2 bg-line sm:-left-1/2 sm:top-5 sm:h-px sm:w-full sm:translate-x-0"></span>
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 transition" :class="step === 2 ? 'border-primary bg-primary text-white ring-4 ring-primary/20' : 'border-line bg-white text-muted'">
                            <span class="text-sm font-bold">2</span>
                            <span x-show="step === 2" class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 animate-pulse rounded-full bg-primary ring-2 ring-white"></span>
                        </div>
                        <div class="min-w-0 sm:mt-2" :class="step === 2 ? '' : 'opacity-80'">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" :class="step === 2 ? 'text-primary-dark' : 'text-muted/70'">2. Langkah 2</p>
                            <p class="mt-0.5 text-sm font-semibold leading-snug" :class="step === 2 ? 'text-ink' : 'text-muted'">Persetujuan Pemagangan</p>
                            <span x-show="step === 2" class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-primary"></span>
                                Sedang mengisi
                            </span>
                            <span x-show="step !== 2" class="mt-1.5 inline-block text-[11px] font-medium text-muted/60">Menunggu</span>
                        </div>
                    </li>
                </ol>
            @endif
        </div>
        @if($readOnly)
            <p class="mt-4 text-xs text-muted">Tahap sebelumnya dapat dilihat di bawah. Pengiriman ulang hanya aktif saat status <b>Perlu perbaikan oleh dosen</b>.</p>
        @elseif(! $application)
            <p class="mt-4 text-xs text-muted">Lengkapi <b class="text-primary-dark">Langkah 1</b> lalu lanjut ke <b class="text-primary-dark">Langkah 2</b> sebelum dikirim.</p>
        @endif
    </section>

    @if($application?->revision_note)
        <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Catatan revisi: {{ $application->revision_note }}</p>
    @endif
    @if($readOnly)
        <p class="mt-4 rounded-2xl bg-bg px-4 py-3 text-sm text-muted">Mode hanya baca — data terkunci mengikuti tahapan yang sedang berjalan.</p>
    @endif

    <section class="mt-5 overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-line px-6 py-6 sm:flex-row sm:items-center md:px-8">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary/15 text-xl font-semibold text-primary-dark">{{ $initials }}</span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Profil dosen</p>
                <h2 class="mt-1 text-xl font-semibold">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $participant->faculty }} · {{ $participant->study_program }}</p>
            </div>
            <a href="{{ route('participant.profile') }}" x-show="step === 1" class="inline-flex items-center justify-center rounded-full border border-line px-4 py-2 text-sm font-semibold sm:ml-auto">Ubah profil</a>
        </div>
        <dl class="grid gap-4 px-6 py-6 text-sm sm:grid-cols-2 md:px-8">
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Email</dt>
                <dd class="mt-1 font-medium">{{ $user->email }}</dd>
            </div>
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Telepon</dt>
                <dd class="mt-1 font-medium">{{ $user->phone ?: 'Belum diisi' }}</dd>
            </div>
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">NIP / NIDN</dt>
                <dd class="mt-1 font-medium">{{ $participant->nidn ?: '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Fakultas / Prodi</dt>
                <dd class="mt-1 font-medium">{{ $participant->faculty }} · {{ $participant->study_program }}</dd>
            </div>
        </dl>
    </section>

    <section class="mt-5 rounded-3xl border border-line bg-white p-6 shadow-sm md:p-8">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">apartment</span>
            </span>
            <div>
                <h2 class="text-lg font-semibold">Penempatan</h2>
                <p class="mt-1 text-sm text-muted">Unit dan departemen terkunci sesuai pilihan Anda.</p>
            </div>
        </div>
        <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Unit bisnis</dt>
                <dd class="mt-1 font-medium">{{ $unit->department?->name ?? '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</dt>
                <dd class="mt-1 font-medium">{{ $unit->name }}</dd>
            </div>
        </dl>
        @if($unit->description)
            <p class="mt-4 text-sm leading-6 text-muted">{{ $unit->description }}</p>
        @endif
    </section>

    @if($canSubmit)
    <form id="application-form" method="POST" action="{{ $application ? route('participant.applications.update', $application) : route('participant.applications.store') }}" class="mt-5 space-y-6 rounded-3xl border border-line bg-white p-6 shadow-sm md:p-8">
        @csrf
        @if($application)
            @method('PUT')
        @endif
        <input type="hidden" name="business_unit_id" value="{{ $unit->id }}">

        <div x-show="step === 1">
    @else
    <section class="mt-5 space-y-6 rounded-3xl border border-line bg-white p-6 shadow-sm md:p-8" aria-readonly="true">
        <div>
    @endif

        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">calendar_month</span>
            </span>
            <div>
                <h2 class="text-lg font-semibold">Periode imersi</h2>
                <p class="mt-1 text-sm text-muted">Durasi otomatis 2 bulan. Ubah tanggal mulai atau selesai, yang lain akan menyesuaikan.</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Tanggal mulai</span>
                <input type="date" name="period_start" x-model="periodStart" @change="onStartChange()" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" {{ $disabled }} required>
            </label>
            <label class="block">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Tanggal selesai</span>
                <input type="date" name="period_end" x-model="periodEnd" @change="onEndChange()" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" {{ $disabled }} required>
            </label>
        </div>

        @if($canSubmit)
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-6">
            <p class="text-sm text-muted">Pastikan periode sudah benar, lalu lanjut mengisi persetujuan pemagangan.</p>
            <button type="button" @click="next()" class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                Lanjut ke persetujuan pemagangan
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </div>
        @endif
        </div>

        @if($canSubmit)
        <div x-show="step === 2">
        @else
        <div>
        @endif
        <div class="border-t border-line pt-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                    <span class="material-symbols-outlined text-[22px]">handshake</span>
                </span>
                <div>
                    <h2 class="text-lg font-semibold">Persetujuan Pemagangan</h2>
                    <p class="mt-1 text-sm text-muted">Sebelum memulai program, peserta dan mentor perlu menyepakati tujuan, aktivitas, output, dari magang dosen.</p>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-line bg-bg p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Informasi Program</p>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold text-muted">Detail Peserta</p>
                        <dl class="mt-2 space-y-1.5 text-sm">
                            <div class="flex justify-between gap-2"><dt class="text-muted">Peserta</dt><dd class="text-right font-medium">{{ $user->name }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">Fakultas</dt><dd class="text-right font-medium">{{ $participant->faculty ?: '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">Program Studi</dt><dd class="text-right font-medium">{{ $participant->study_program ?: '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">Email</dt><dd class="text-right font-medium">{{ $user->email }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">No. HP</dt><dd class="text-right font-medium">{{ $user->phone ?: '—' }}</dd></div>
                        </dl>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-muted">Detail Lokasi</p>
                        <dl class="mt-2 space-y-1.5 text-sm">
                            <div class="flex justify-between gap-2"><dt class="text-muted">Unit Bisnis</dt><dd class="text-right font-medium">{{ $unit->department?->name ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">Department</dt><dd class="text-right font-medium">{{ $unit->name }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-muted">Periode</dt><dd class="text-right font-medium" x-text="formatPeriod()">{{ $application?->periodLabel() ?? ($periodStart.' – '.$periodEnd) }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            @php
                $checkedActivities = old('activity_types', $application?->normalizedActivityTypes() ?? []);
                $indicatorSeed = array_values(array_filter(array_map(
                    fn ($item) => trim((string) $item),
                    (array) old('success_indicators', $application?->normalizedSuccessIndicators() ?? [])
                ), fn ($item) => $item !== ''));
                while (count($indicatorSeed) < \App\Models\Application::MIN_SUCCESS_INDICATORS) {
                    $indicatorSeed[] = '';
                }
            @endphp

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-line bg-bg p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 1 — Shared Goal</span>
                    <p class="mt-1 text-sm text-muted">Selama 2 bulan, kami akan __________ untuk menghasilkan __________ yang memberikan manfaat bagi __________.</p>
                    <label class="mt-3 block">
                        <span class="text-sm font-medium">Shared Goal*</span>
                        <textarea name="shared_goal" rows="3" placeholder="Selama 2 bulan, kami akan ... untuk menghasilkan ... yang memberikan manfaat bagi ..." class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required minlength="20">{{ old('shared_goal', $application?->shared_goal) }}</textarea>
                    </label>
                </div>

                <div class="rounded-2xl border border-line bg-bg p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 2 — Activity</span>
                    <p class="mt-1 text-sm font-medium">Peserta dan mentor menentukan aktivitas. Isi Pilihan 1 (wajib) dan Pilihan 2 bila ada aktivitas pendukung.</p>
                    @php
                        $activityChoices = array_pad(array_values((array) $checkedActivities), 2, '');
                    @endphp
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium">Pilihan 1*</span>
                            <select name="activity_types[]" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required>
                                <option value="" disabled @selected($activityChoices[0] === '')>Pilih aktivitas</option>
                                @foreach(\App\Models\Application::ACTIVITY_TYPES as $type)
                                    <option value="{{ $type }}" @selected($activityChoices[0] === $type)>{{ \App\Models\Application::activityTypeLabel($type) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium">Pilihan 2</span>
                            <select name="activity_types[]" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }}>
                                <option value="">Opsional</option>
                                @foreach(\App\Models\Application::ACTIVITY_TYPES as $type)
                                    <option value="{{ $type }}" @selected($activityChoices[1] === $type)>{{ \App\Models\Application::activityTypeLabel($type) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-line bg-bg p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 3 — Problem / Opportunity</span>
                    <label class="mt-1 block">
                        <span class="text-sm font-medium">Problem / Opportunity Statement*</span>
                        <span class="mt-0.5 block text-xs text-muted">Jelaskan problem atau peluang yang akan menjadi fokus selama Industry Immersion.</span>
                        <textarea name="problem_statement" rows="4" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required minlength="20">{{ old('problem_statement', $application?->problem_statement) }}</textarea>
                    </label>
                </div>

                <div class="rounded-2xl border border-line bg-bg p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 4 — Main Output</span>
                    <label class="mt-1 block">
                        <span class="text-sm font-medium">Main Output*</span>
                        <span class="mt-0.5 block text-xs text-muted">Apa hasil utama yang akan dihasilkan selama program? Misalnya: Research Report + Prototype Concept</span>
                        <textarea name="main_output" rows="3" placeholder="Misalnya: Research Report + Prototype Concept" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required minlength="10">{{ old('main_output', $application?->main_output) }}</textarea>
                    </label>
                </div>

                <div class="rounded-2xl border border-line bg-bg p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 5 — Benefit</span>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium">Benefit untuk Dosen / TSU*</span>
                            <textarea name="participant_benefit" rows="3" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required minlength="20">{{ old('participant_benefit', $application?->participant_benefit) }}</textarea>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium">Benefit untuk Unit Bisnis*</span>
                            <textarea name="business_benefit" rows="3" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }} required minlength="20">{{ old('business_benefit', $application?->business_benefit) }}</textarea>
                        </label>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-line bg-bg p-4"
                    x-data="{
                        indicators: @js($indicatorSeed),
                        max: {{ \App\Models\Application::MAX_SUCCESS_INDICATORS }},
                        min: {{ \App\Models\Application::MIN_SUCCESS_INDICATORS }},
                        add() {
                            if (this.indicators.length < this.max) this.indicators.push('');
                        },
                        remove(index) {
                            if (this.indicators.length > this.min) this.indicators.splice(index, 1);
                        }
                    }"
                >
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 6 — Success Indicators</span>
                    <p class="mt-1 text-sm font-medium">Isi minimal {{ \App\Models\Application::MIN_SUCCESS_INDICATORS }} indikator keberhasilan. Tambah baris bila perlu (maks. {{ \App\Models\Application::MAX_SUCCESS_INDICATORS }}).*</p>
                    <p class="mt-0.5 text-xs text-muted">Mentor dapat mengomentari berulang kali lewat minta revisi jika belum puas.</p>
                    <div class="mt-3 space-y-3">
                        <template x-for="(indicator, index) in indicators" :key="index">
                            <label class="block">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-muted" x-text="(index + 1) + '.'"></span>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-red-600 disabled:opacity-40"
                                        @click="remove(index)"
                                        x-show="indicators.length > min"
                                        {{ $disabled ? 'disabled' : '' }}
                                    >Hapus</button>
                                </div>
                                <input
                                    type="text"
                                    name="success_indicators[]"
                                    x-model="indicators[index]"
                                    maxlength="255"
                                    class="mt-1 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15"
                                    {{ $disabled }}
                                    :required="index < min"
                                >
                            </label>
                        </template>
                    </div>
                    @unless($readOnly)
                        <button
                            type="button"
                            class="mt-3 inline-flex items-center gap-1 rounded-full border border-line bg-white px-4 py-2 text-xs font-semibold disabled:opacity-40"
                            @click="add()"
                            :disabled="indicators.length >= max"
                        >
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Tambah indikator
                        </button>
                    @endunless
                </div>

                @if($application)
                    <div class="rounded-2xl border border-line bg-bg p-4">
                        <label class="block">
                            <span class="text-sm font-medium">Feedback untuk usulan mentor (opsional)</span>
                            <span class="mt-0.5 block text-xs text-muted">Gunakan kolom ini untuk menanggapi perubahan indikator yang diusulkan mentor sebelum mengirim ulang.</span>
                            <textarea name="indicator_feedback" rows="3" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" {{ $disabled }}>{{ old('indicator_feedback', $application?->indicator_feedback) }}</textarea>
                        </label>
                    </div>
                @endif
            </div>
        </div>

        @if($canSubmit)
        <div class="flex flex-wrap items-start gap-4 rounded-2xl bg-bg px-4 py-4">
            <label class="flex min-w-0 flex-1 items-start gap-3 text-sm">
                <input type="checkbox" name="declaration" value="1" class="mt-1" @checked(old('declaration')) required>
                <span>Saya telah membaca ketentuan di atas, menyatakan data benar, dan mengajukan persetujuan pemagangan untuk ditinjau admin, mentor, lalu disahkan admin.</span>
            </label>
        </div>

        <x-signature-pad
            class="mt-4"
            name="participant_signature"
            label="Tanda tangan dosen"
            hint="Setelah membaca ketentuan, coret tanda tangan di kotak atau unggah gambar PNG/JPG."
            :required="true"
            :value="old('participant_signature', $application?->participant_signature)"
        />

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <button type="button" @click="back()" class="inline-flex items-center gap-1 rounded-full border border-line px-5 py-2.5 text-sm font-semibold">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke data diri
            </button>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">send</span>
                {{ $application ? 'Kirim ulang ke admin' : 'Kirim pendaftaran & persetujuan' }}
            </button>
        </div>
        @else
        <div class="mt-4 space-y-4">
            @if($application?->participant_signature)
                <x-signature-pad
                    name="participant_signature_preview"
                    label="Tanda tangan dosen"
                    hint="Tanda tangan yang sudah dikirim."
                    :disabled="true"
                    :value="$application->participant_signature"
                />
            @endif
            <div class="rounded-2xl border border-line bg-bg px-4 py-4 text-sm text-muted">
                <p class="font-semibold text-ink">Pengiriman dikunci</p>
                <p class="mt-1">Anda hanya dapat melihat data tahapan ini. Form dapat dikirim ulang hanya setelah admin meminta revisi.</p>
                <a href="{{ route('participant.applications.show', $application) }}" class="mt-3 inline-flex items-center gap-1 font-semibold text-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke status pendaftaran
                </a>
            </div>
        </div>
        @endif
        </div>
    @if($canSubmit)
    </form>
    @else
    </section>
    @endif
</div>
@endsection
