@extends('layouts.form')
@section('title', $application ? 'Perbaiki Pendaftaran' : 'Daftar Program')
@section('content')
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $competencies = collect($participant->competency ?? [])->filter()->values();
    $expertise = collect($participant->expertise ?? [])->filter()->values();
@endphp

<div>
    <a href="{{ $unit ? route('units.show', $unit) : route('departments.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Kembali
    </a>

    <section class="mt-5 overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="hero-grid px-6 py-8 md:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Pendaftaran dosen</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white md:text-4xl">{{ $application ? 'Perbaiki form pendaftaran' : 'Form pendaftaran program' }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-white/75">Lengkapi profil, periode, pertanyaan, dan CV. Alur: dosen → admin → mentor → admin → dosen.</p>
        </div>
    </section>

    @if($application?->revision_note)
        <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Catatan revisi: {{ $application->revision_note }}</p>
    @endif

    <section class="mt-5 overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-line px-6 py-6 sm:flex-row sm:items-center md:px-8">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary/15 text-xl font-semibold text-primary-dark">{{ $initials }}</span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Profil dosen</p>
                <h2 class="mt-1 text-xl font-semibold">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $participant->faculty }} · {{ $participant->study_program }}</p>
            </div>
            <a href="{{ route('participant.profile') }}" class="inline-flex items-center justify-center rounded-full border border-line px-4 py-2 text-sm font-semibold sm:ml-auto">Ubah profil</a>
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
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Kompetensi</dt>
                <dd class="mt-2 flex flex-wrap gap-2">
                    @forelse($competencies as $item)
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 ring-line">{{ $item }}</span>
                    @empty
                        <span class="text-muted">Belum diisi</span>
                    @endforelse
                </dd>
            </div>
            <div class="rounded-2xl bg-bg p-4">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Keahlian</dt>
                <dd class="mt-2 flex flex-wrap gap-2">
                    @forelse($expertise as $item)
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 ring-line">{{ $item }}</span>
                    @empty
                        <span class="text-muted">Belum diisi</span>
                    @endforelse
                </dd>
            </div>
            <div class="rounded-2xl bg-bg p-4 sm:col-span-2">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Pengalaman relevan</dt>
                <dd class="mt-1 leading-6">{{ $participant->experience ?: 'Belum diisi' }}</dd>
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

    <form method="POST" action="{{ $application ? route('participant.applications.update', $application) : route('participant.applications.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6 rounded-3xl border border-line bg-white p-6 shadow-sm md:p-8">
        @csrf
        @if($application)
            @method('PUT')
        @endif
        <input type="hidden" name="business_unit_id" value="{{ $unit->id }}">

        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                <span class="material-symbols-outlined text-[22px]">calendar_month</span>
            </span>
            <div>
                <h2 class="text-lg font-semibold">Periode imersi</h2>
                <p class="mt-1 text-sm text-muted">Durasi otomatis 2 bulan. Ubah tanggal mulai atau selesai, yang lain akan menyesuaikan.</p>
            </div>
        </div>
        <div
            class="grid gap-4 sm:grid-cols-2"
            x-data="{
                start: @js(old('period_start', $periodStart)),
                end: @js(old('period_end', $periodEnd)),
                addMonths(value, months) {
                    if (! value) return '';
                    const parts = value.split('-').map(Number);
                    const date = new Date(Date.UTC(parts[0], parts[1] - 1 + months, 1));
                    const last = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 0)).getUTCDate();
                    date.setUTCDate(Math.min(parts[2], last));
                    return date.toISOString().slice(0, 10);
                },
                onStartChange() {
                    if (this.start) {
                        this.end = this.addMonths(this.start, 2);
                    }
                },
                onEndChange() {
                    if (this.end) {
                        this.start = this.addMonths(this.end, -2);
                    }
                }
            }"
        >
            <label class="block">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Tanggal mulai</span>
                <input type="date" name="period_start" x-model="start" @change="onStartChange()" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
            </label>
            <label class="block">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Tanggal selesai</span>
                <input type="date" name="period_end" x-model="end" @change="onEndChange()" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
            </label>
        </div>

        <div class="border-t border-line pt-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                    <span class="material-symbols-outlined text-[22px]">quiz</span>
                </span>
                <div>
                    <h2 class="text-lg font-semibold">Pertanyaan pendaftaran</h2>
                    <p class="mt-1 text-sm text-muted">Jawab 5 pertanyaan berikut. Setiap jawaban minimal 20 karakter.</p>
                </div>
            </div>
            <div class="mt-5 space-y-4">
                @foreach(\App\Models\Application::registrationQuestions() as $index => $question)
                    @php
                        $default = $application?->{$question['key']} ?? ($question['key'] === 'motivation' ? $participant->motivation : '');
                    @endphp
                    <label class="block rounded-2xl border border-line bg-bg p-4">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Pertanyaan {{ $index + 1 }}</span>
                        <span class="mt-1 block text-sm font-medium">{{ $question['label'] }}</span>
                        <textarea name="{{ $question['key'] }}" rows="4" class="mt-3 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" required minlength="20">{{ old($question['key'], $default) }}</textarea>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="border-t border-line pt-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                    <span class="material-symbols-outlined text-[22px]">upload_file</span>
                </span>
                <div>
                    <h2 class="text-lg font-semibold">Curriculum Vitae</h2>
                    <p class="mt-1 text-sm text-muted">Wajib unggah CV dalam PDF atau Word, maksimal 5 MB.</p>
                </div>
            </div>
            <label class="mt-5 block rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-5">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Unggah CV</span>
                <input type="file" name="cv" accept=".pdf,.doc,.docx,application/pdf" class="mt-3 w-full text-sm" @required(! $application?->cv_path)>
                @if($application?->cvUrl())
                    <a href="{{ $application->cvUrl() }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary-dark" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined text-[18px]">description</span>
                        CV yang sudah diunggah
                    </a>
                @endif
            </label>
        </div>

        <label class="flex items-start gap-3 rounded-2xl bg-bg px-4 py-3 text-sm">
            <input type="checkbox" name="declaration" value="1" class="mt-1" @checked(old('declaration')) required>
            <span>Saya menyatakan data di atas benar dan mengajukan surat persetujuan untuk ditinjau admin, mentor, lalu disahkan admin.</span>
        </label>

        <button class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark sm:w-auto">
            <span class="material-symbols-outlined text-[18px]">send</span>
            {{ $application ? 'Kirim ulang ke admin' : 'Kirim pendaftaran & surat persetujuan' }}
        </button>
    </form>
</div>
@endsection
