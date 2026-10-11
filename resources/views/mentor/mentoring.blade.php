@extends('layouts.app')
@section('title', 'Mentoring Mingguan')
@section('content')
<p class="text-sm text-muted">Riwayat sesi yang dicatat peserta. Mentor cukup melihat — tanpa persetujuan.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b border-line text-left text-muted">
                    <th class="w-12 px-5 py-3 font-medium">No</th>
                    <th class="px-5 py-3 font-medium">Peserta</th>
                    <th class="px-5 py-3 font-medium">Tanggal</th>
                    <th class="px-5 py-3 font-medium">Aktivitas</th>
                    <th class="w-44 px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr class="border-b border-line last:border-0" x-data="{ detailOpen: false }">
                        <td class="px-5 py-3">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3 font-medium">{{ $session->program?->participant?->user?->name ?? '—' }}</td>
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
                                            <p class="mt-0.5 text-sm text-muted">{{ $session->program?->participant?->user?->name ?? '—' }} · {{ $session->session_date?->format('d M Y') ?? '—' }} · Minggu {{ $session->week }}</p>
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
                                    </dl>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-muted">Belum ada sesi mentoring dari peserta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
