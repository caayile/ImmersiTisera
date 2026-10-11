@extends('layouts.app')
@section('title', 'Program Saya')
@section('content')
@php
    $next = ['Lengkapi profil kompetensi Anda.', route('participant.profile')];
    if ($participant?->study_program) {
        $next = ['Ajukan program ke unit bisnis yang relevan.', route('departments.index')];
    }
    if ($application && ! $program) {
        $next = match ($application->status) {
            'submitted' => ['Pendaftaran menunggu tinjauan admin.', route('participant.applications.show', $application)],
            'waiting_mentor' => ['Menunggu persetujuan mentor industri.', route('participant.applications.show', $application)],
            'waiting_admin' => ['Menunggu pengesahan akhir dari admin.', route('participant.applications.show', $application)],
            'revision' => ['Perbaiki formulir pendaftaran sesuai catatan peninjau.', route('participant.applications.edit', $application)],
            'rejected' => ['Pendaftaran ditolak. Ajukan program lain jika masih relevan.', route('participant.applications')],
            'approved' => ['Pendaftaran disetujui. Lanjutkan ke perjanjian magang dosen.', route('participant.agreement')],
            default => $next,
        };
    }
    if ($program) {
        $next = match ($program->status) {
            'draft', 'submitted' => ['Lengkapi dan ajukan Perjanjian Magang Dosen.', route('participant.agreement')],
            'revision' => ['Perbaiki perjanjian sesuai catatan mentor.', route('participant.agreement')],
            'agreed' => ['Menunggu program diaktifkan setelah perjanjian disepakati.', route('participant.agreement')],
            'active' => ['Isi logbook harian dan siapkan mentoring minggu ini.', route('participant.logbooks')],
            'completed' => ['Tentukan tindak lanjut kolaborasi setelah magang.', route('participant.collaboration')],
            default => $next,
        };
    }
    $logbookCount = $program?->logbooks?->count() ?? 0;
    $checkpointDone = $program?->timelines?->whereNotIn('status', ['pending', 'draft'])->count() ?? 0;
    $mentoringCount = $program?->mentorSessions?->count() ?? 0;
    $agreementLabel = \App\Support\Status::label($program?->agreement?->status ?? 'draft');
    $progress = (int) ($program?->progress ?? 0);
    $week = (int) ($program?->current_week ?? 1);
    $mentorPhone = $program?->mentor?->user?->phone ? preg_replace('/\D+/', '', (string) $program->mentor->user->phone) : '';
    if ($mentorPhone && str_starts_with($mentorPhone, '0')) {
        $mentorPhone = '62'.substr($mentorPhone, 1);
    }
@endphp

@unless($program)
    <x-empty class="mt-6" title="Belum ada program" :action="route('departments.index')" label="Daftar program">Pengajuan Anda akan menjadi program setelah surat persetujuan disahkan admin.</x-empty>
    <div class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-line bg-white px-5 py-4 shadow-sm">
        <p class="text-sm text-muted">{{ $next[0] }}</p>
        <a href="{{ $next[1] }}" class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lanjutkan</a>
    </div>
@else
    {{-- Ringkasan atas: Total Magang + Mentor --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-[220px_1fr]">
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <p class="flex items-center gap-2 text-sm text-ink">
                <span class="material-symbols-outlined text-[20px] text-muted">work</span>
                Total Magang
            </p>
            <p class="mt-2 text-center text-2xl font-semibold">{{ $stats['programs'] ?? 0 }}</p>
        </article>

        <section class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between" x-data="{ mentorOpen: false }">
            <div class="min-w-0">
                <p class="text-sm font-medium text-primary-dark">Mentor</p>
                <h2 class="mt-0.5 text-lg font-semibold">{{ $program->mentor?->user?->name ?? 'Belum ditugaskan' }}</h2>
                <p class="mt-1 text-sm text-muted">Mentor industri untuk program magang Anda. Silakan berkoordinasi langsung dengan mentor.</p>
            </div>
            @if($program->mentor?->user)
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" @click="mentorOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-primary/40 px-4 py-2.5 text-sm font-medium text-primary-dark transition hover:bg-primary/5">
                        <span class="material-symbols-outlined text-[19px]">person</span>
                        Lihat Profil
                    </button>
                </div>

                <div x-show="mentorOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="mentorOpen && 'flex'" @click.self="mentorOpen = false" @keydown.escape.window="mentorOpen = false" role="dialog" aria-modal="true" aria-label="Profil mentor">
                    <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-xl">
                        <div class="hero-grid px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="min-w-0 text-white">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-secondary">Mentor Industri</p>
                                    <h3 class="mt-0.5 truncate text-xl font-semibold">{{ $program->mentor->user->name }}</h3>
                                    <p class="mt-0.5 truncate text-sm text-white/75">{{ $program->mentor->user->email }}</p>
                                </div>
                                <button type="button" @click="mentorOpen = false" class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white transition hover:bg-white/25" aria-label="Tutup">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>
                        </div>
                        <dl class="grid gap-3 p-6 text-sm sm:grid-cols-2">
                            <div class="rounded-xl bg-bg px-3 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Telepon</dt>
                                <dd class="mt-0.5 font-medium">{{ $program->mentor->user->phone ?: '—' }}</dd>
                            </div>
                            <div class="rounded-xl bg-bg px-3 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Posisi</dt>
                                <dd class="mt-0.5 font-medium">{{ $program->mentor->position ?: 'Mentor Industri' }}</dd>
                            </div>
                            <div class="rounded-xl bg-bg px-3 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Unit Bisnis</dt>
                                <dd class="mt-0.5 font-medium">{{ $program->mentor->department?->name ?: '—' }}</dd>
                            </div>
                            <div class="rounded-xl bg-bg px-3 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Departemen</dt>
                                <dd class="mt-0.5 font-medium">{{ $program->mentor->businessUnit?->name ?: '—' }}</dd>
                            </div>
                            @if($mentorPhone)
                                <div class="sm:col-span-2">
                                    <a href="https://wa.me/{{ $mentorPhone }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-[#159447] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#107c3a]">
                                        <span class="material-symbols-outlined text-[19px]">chat</span>
                                        Buka WhatsApp
                                    </a>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- Informasi Program + Jejak Program --}}
    <article class="mt-4 grid gap-6 rounded-2xl border border-line bg-white p-6 shadow-sm lg:grid-cols-[1fr_280px]">
        <div>
            <h2 class="text-xl font-medium tracking-[0.08em]">Informasi Program</h2>
            <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm tracking-[0.08em] text-muted">Unit Bisnis</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $program->department?->name ?? 'Belum dipilih' }}</dd>
                </div>
                <div>
                    <dt class="text-sm tracking-[0.08em] text-muted">Departemen</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $program->businessUnit?->name ?? 'Belum dipilih' }}</dd>
                </div>
                <div>
                    <dt class="text-sm tracking-[0.08em] text-muted">Mentor</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $program->mentor?->user?->name ?? 'Belum ditugaskan' }}</dd>
                </div>
                <div>
                    <dt class="text-sm tracking-[0.08em] text-muted">Periode</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $program->start_date?->format('d M Y') ?? '—' }} – {{ $program->end_date?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm tracking-[0.08em] text-muted">Progress Magang</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $progress }}% · Minggu {{ $week }}</dd>
                    <div class="mt-2 h-2 max-w-md overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </dl>
        </div>
        <div class="border-t border-line pt-5 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
            <h3 class="text-lg font-medium tracking-[0.08em]">Jejak Program</h3>
            <ul class="mt-4 space-y-3 text-lg">
                <li class="flex items-baseline gap-2">
                    <span class="text-sm tracking-[0.08em] text-muted">Perjanjian :</span>
                    <span class="font-medium">{{ $agreementLabel }}</span>
                </li>
                <li class="flex items-baseline gap-2">
                    <span class="text-sm tracking-[0.08em] text-muted">Logbook :</span>
                    <span class="font-medium">{{ $logbookCount }}</span>
                </li>
                <li class="flex items-baseline gap-2">
                    <span class="text-sm tracking-[0.08em] text-muted">Checkpoint :</span>
                    <span class="font-medium">{{ $checkpointDone }}</span>
                </li>
                <li class="flex items-baseline gap-2">
                    <span class="text-sm tracking-[0.08em] text-muted">Mentoring :</span>
                    <span class="font-medium">{{ $mentoringCount }}</span>
                </li>
            </ul>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <x-badge :status="$program->status" />
            </div>
        </div>
    </article>

    {{-- Tambahan relevan: langkah selanjutnya --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white px-5 py-4 shadow-sm">
        <p class="text-sm text-muted">{{ $next[0] }}</p>
        <a href="{{ $next[1] }}" class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lanjutkan</a>
    </div>

    {{-- Tambahan relevan: aksi cepat --}}
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['edit_note', 'Logbook', 'Isi refleksi harian', route('participant.logbooks')],
            ['forum', 'Mentoring', 'Jadwal & sesi mentor', route('participant.mentoring')],
            ['fact_check', 'Checkpoint', '4 fase magang', route('participant.timeline')],
            ['summarize', 'Laporan Akhir', 'Hasil & laporan', route('participant.final-report')],
        ] as [$icon, $title, $copy, $url])
            <a href="{{ $url }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                    <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                </span>
                <p class="mt-4 font-semibold">{{ $title }}</p>
                <p class="mt-1 text-sm text-muted">{{ $copy }}</p>
            </a>
        @endforeach
    </div>
@endunless
@endsection
