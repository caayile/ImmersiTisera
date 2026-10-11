@extends('layouts.app')
@section('title', 'Mentoring Mingguan')
@section('content')
<div class="flex flex-wrap items-center justify-end gap-3" x-data="{ createOpen: false }">
    @if($program?->status === 'active')
        <button type="button" @click="createOpen = true" class="inline-flex items-center gap-1.5 rounded-xl bg-[#22b07d] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">
            <span class="text-lg leading-none">+</span>
            Sesi Mentoring
        </button>
    @endif

    {{-- Popup Sesi Mentoring --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="createOpen && 'flex'" @click.self="createOpen = false" @keydown.escape.window="createOpen = false" role="dialog" aria-modal="true" aria-label="Sesi mentoring baru">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Sesi Mentoring</h2>
                    <p class="mt-0.5 text-sm text-muted">Aktivitas Mentoring</p>
                </div>
                <button type="button" @click="createOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
            </div>

            <form method="POST" enctype="multipart/form-data" action="{{ route('participant.mentoring.store') }}" class="mt-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="mentoring-date" class="text-sm font-semibold">Tanggal Mentoring</label>
                        <input id="mentoring-date" type="date" name="session_date" value="{{ old('session_date', now()->toDateString()) }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label for="mentoring-activity" class="text-sm font-semibold">Aktivitas</label>
                        <input id="mentoring-activity" name="activity" value="{{ old('activity') }}" maxlength="180" placeholder="Tuliskan aktivitas mentoring" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400" required>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="mentoring-findings" class="text-sm font-semibold">Apa yang sudah ditemukan</label>
                    <textarea id="mentoring-findings" name="findings" rows="2" placeholder="Jelaskan temuan dalam 1 pekan ini" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">{{ old('findings') }}</textarea>
                </div>
                <div class="mt-4">
                    <label for="mentoring-work" class="text-sm font-semibold">Apa yang sudah dikerjakan</label>
                    <textarea id="mentoring-work" name="current_work" rows="2" placeholder="Tuliskan apa saja yang sudah dikerjakan 1 pekan ini" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">{{ old('current_work') }}</textarea>
                </div>
                <div class="mt-4">
                    <label for="mentoring-next" class="text-sm font-semibold">Apa langkah berikutnya</label>
                    <textarea id="mentoring-next" name="next_action" rows="2" placeholder="Jelaskan apa langkah dalam 1 pekan kedepan" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">{{ old('next_action') }}</textarea>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-semibold">Bukti Pendukung</p>
                    <input name="evidence_link" value="{{ old('evidence_link') }}" inputmode="url" placeholder="Link Bukti Pendukung (G-Drive, dll)" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">
                    <div class="mt-3" x-data="{ fileName: '' }">
                        <input id="mentoring-file" type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" class="sr-only" @change="fileName = $event.target.files[0]?.name ?? ''">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-2.5">
                            <span class="truncate text-sm text-muted" x-text="fileName || 'Pilih File Dokumen'">Pilih File Dokumen</span>
                            <label for="mentoring-file" class="shrink-0 cursor-pointer rounded-lg bg-mint-light px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/15">Pilih File</label>
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-muted">Maksimal 10 MB.</p>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="rounded-xl border border-line px-5 py-2.5 text-sm font-semibold text-muted transition hover:bg-slate-50">Batal</button>
                    <button class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

@unless($program)
    <x-empty class="mt-6" title="Belum ada sesi mentoring">Sesi mentoring muncul setelah program dimulai.</x-empty>
@else
    @if($program->status !== 'active')
        <p class="mt-4 rounded-xl border border-dashed border-line bg-white/60 px-4 py-3 text-sm text-muted">Program belum aktif — pengisian sesi mentoring dibuka setelah program berjalan.</p>
    @endif
    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="border-b border-line text-left text-muted">
                        <th class="w-12 px-5 py-3 font-medium">No</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Aktivitas</th>
                        <th class="w-44 px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr class="border-b border-line last:border-0" x-data="{ detailOpen: false }">
                            <td class="px-5 py-3">{{ $loop->iteration }}</td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $session->session_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3 font-medium">{{ $session->activity ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <button type="button" @click="detailOpen = true" class="inline-flex items-center gap-1.5 rounded-full border border-primary/40 px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/5">
                                    <span class="material-symbols-outlined text-[15px]">info</span>
                                    Detail Informasi
                                </button>

                                {{-- Popup Detail Informasi --}}
                                <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="detailOpen && 'flex'" @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" role="dialog" aria-modal="true" aria-label="Detail sesi mentoring">
                                    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 text-left shadow-xl sm:p-8">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h2 class="text-lg font-semibold">Sesi Mentoring</h2>
                                                <p class="mt-0.5 text-sm text-muted">{{ $session->session_date?->format('d M Y') ?? '—' }} · Minggu {{ $session->week }}</p>
                                            </div>
                                            <button type="button" @click="detailOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
                                        </div>
                                        <dl class="mt-5 space-y-4 text-sm">
                                            <div>
                                                <dt class="font-semibold">Aktivitas</dt>
                                                <dd class="mt-1 rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $session->activity ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold">Apa yang sudah ditemukan</dt>
                                                <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $session->findings ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold">Apa yang sudah dikerjakan</dt>
                                                <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $session->current_work ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold">Apa langkah berikutnya</dt>
                                                <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $session->next_action ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold">Bukti Pendukung</dt>
                                                <dd class="mt-1 space-y-1.5">
                                                    @if($session->evidence_link)
                                                        <a href="{{ $session->evidence_link }}" target="_blank" rel="noopener" class="block truncate font-medium text-primary-dark">{{ $session->evidence_link }}</a>
                                                    @endif
                                                    @if($session->attachment_path)
                                                        <a href="{{ asset('storage/'.$session->attachment_path) }}" target="_blank" rel="noopener" class="inline-block font-semibold text-primary-dark">Unduh bukti terlampir</a>
                                                    @endif
                                                    @unless($session->evidence_link || $session->attachment_path)
                                                        <span class="text-muted">—</span>
                                                    @endunless
                                                </dd>
                                            </div>
                                            @if($session->feedback)
                                                <div>
                                                    <dt class="font-semibold">Masukan mentor</dt>
                                                    <dd class="mt-1 rounded-xl bg-amber-50 px-4 py-2.5 text-amber-800">{{ $session->feedback }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-muted">Belum ada sesi mentoring. Klik <b>+ Sesi Mentoring</b> untuk mencatat yang pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endunless
@endsection
