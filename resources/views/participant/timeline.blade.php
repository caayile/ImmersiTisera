@extends('layouts.app')
@section('title', 'Checkpoint')
@section('content')
@unless($program)
    <x-empty class="mt-6" title="Checkpoint belum aktif">Checkpoint muncul setelah program dimulai.</x-empty>
@else
@php
    $currentWeek = $program->computedWeek();
    $phaseNames = [1 => 'Orientasi', 2 => 'OBSERVASI', 5 => 'KOLABORASI', 8 => 'HASIL'];
    $phasePlaceholders = [
        1 => ['Masukkan judul kegiatan di masa orientasi', 'Jelaskan apa yang telah dilakukan/kerjakan dalam masa orientasi. Ceritakan tugas atau aktivitas yang diselesaikan dengan deskripsi yang jelas.'],
        2 => ['Masukkan judul kegiatan di masa observasi', 'Jelaskan apa yang telah diamati dalam masa observasi. Ceritakan temuan hambatan atau peluang beserta validasi bersama mentor dengan deskripsi yang jelas.'],
        5 => ['Masukkan judul kegiatan di masa kolaborasi', 'Jelaskan apa yang telah dikerjakan dalam masa kolaborasi. Ceritakan tugas bersama atau kontribusi beserta kemajuannya dengan deskripsi yang jelas.'],
        8 => ['Masukkan judul laporan atau hasil akhir', 'Jelaskan apa yang telah diselesaikan sebagai laporan atau hasil utama. Ceritakan presentasi dan refleksi penutupan dengan deskripsi yang jelas.'],
    ];
@endphp
<div class="mt-6 flex flex-wrap items-center gap-x-10 gap-y-2 rounded-2xl border border-line bg-white px-5 py-4 shadow-sm">
    <div>
        <p class="text-sm tracking-[0.08em] text-muted">Penempatan</p>
        <p class="mt-0.5 text-lg font-medium">{{ $program->department?->name ?? '—' }} · {{ $program->businessUnit?->name ?? '—' }}</p>
    </div>
    <div>
        <p class="text-sm tracking-[0.08em] text-muted">Minggu Berjalan</p>
        <p class="mt-0.5 text-lg font-medium">Minggu {{ $currentWeek }} / 8</p>
    </div>
    <div class="ml-auto"><x-badge :status="$program->status" /></div>
</div>

<div class="mt-8">
    @forelse($program->timelines->whereIn('week', \App\Support\Status::CHECKPOINT_WEEKS)->sortBy('week') as $item)
        @php
            $week = (int) $item->week;
            $windowLabel = \App\Support\Status::checkpointWindowLabel($week);
            $phaseName = $phaseNames[$week] ?? \App\Support\Status::timelineText($item->title);
            $isOpen = $program->isCheckpointOpen($week);
            $isFuture = $currentWeek < $week;
            $isDone = in_array($item->status, ['done', 'approved'], true);
            $reviewPill = match ($item->status) {
                'submitted' => ['Diajukan', 'bg-sky-100 text-sky-800'],
                'done', 'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800'],
                'revision' => ['Revisi', 'bg-amber-100 text-amber-800'],
                default => ['Menunggu', 'bg-slate-100 text-slate-600'],
            };
            $hasUserContent = ! \App\Support\Status::isLegacyCheckpointLabel($item->title) || filled($item->description) || filled($item->evidence_link) || filled($item->attachment_path);
            $formTitle = \App\Support\Status::isLegacyCheckpointLabel($item->title) ? '' : $item->title;
            $formDescription = \App\Support\Status::isLegacyCheckpointLabel($item->description) ? '' : $item->description;
            [$judulPlaceholder, $deskripsiPlaceholder] = $phasePlaceholders[$week] ?? ['Masukkan judul kegiatan', 'Jelaskan aktivitas yang dilakukan dengan deskripsi yang jelas.'];
        @endphp
        <div class="flex gap-4" x-data="{ open: false }">
            <div class="flex flex-col items-center">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-semibold {{ $isOpen || $isDone ? 'bg-primary text-white' : 'border border-line bg-white text-ink' }}">{{ $loop->iteration }}</span>
                @if(! $loop->last)
                    <span class="w-0.5 flex-1 {{ $isDone ? 'bg-primary/60' : 'bg-primary/40' }}"></span>
                @endif
            </div>
            <article class="mb-8 flex-1 rounded-2xl border border-primary/60 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-sm font-semibold tracking-[0.08em]">Minggu {{ $windowLabel === 'Minggu 1' ? '1' : ($windowLabel === 'Minggu 8' ? '8' : ($week === 2 ? '2-4' : '5-7')) }} · {{ $phaseName }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($isDone)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Terisi</span>
                        @elseif($isOpen)
                            <span class="rounded-full bg-primary/15 px-3 py-1 text-[11px] font-semibold text-primary-dark">Dibuka</span>
                        @elseif($isFuture)
                            <span class="rounded-full bg-slate-200/80 px-3 py-1 text-[11px] font-semibold text-slate-600">Terkunci</span>
                        @else
                            <span class="rounded-full bg-slate-200/80 px-3 py-1 text-[11px] font-semibold text-slate-600">Ditutup</span>
                        @endif
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $reviewPill[1] }}">{{ $reviewPill[0] }}</span>
                    </div>
                </div>

                @if($hasUserContent && $formTitle)
                    <p class="mt-2 text-sm font-medium">{{ $formTitle }}</p>
                @endif
                @if($hasUserContent && $formDescription)
                    <p class="mt-1 text-sm text-muted">{{ $formDescription }}</p>
                @endif
                @if($item->evidence_link)
                    <a href="{{ $item->evidence_link }}" target="_blank" rel="noopener" class="mt-1 block truncate text-sm font-medium text-primary-dark">{{ $item->evidence_link }}</a>
                @endif
                @if($item->attachment_path)
                    <a href="{{ asset('storage/'.$item->attachment_path) }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm font-semibold text-primary-dark">Unduh bukti terlampir</a>
                @endif
                @if($item->mentor_note)
                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800"><b>Catatan mentor:</b> {{ $item->mentor_note }}</p>
                @endif

                <div class="mt-3">
                    @if($isDone)
                        <button type="button" @click="open = true" class="rounded-xl border border-primary/40 bg-white px-5 py-2.5 text-sm font-semibold tracking-[0.06em] text-primary-dark transition hover:bg-primary/5">Lihat Checkpoint</button>
                        <p class="mt-2 text-xs text-muted">Sudah disetujui mentor dan tidak dapat diubah lagi.</p>
                    @elseif($isOpen)
                        <button type="button" @click="open = true" class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold tracking-[0.06em] text-white transition hover:bg-[#1a9a6d]">Isi Checkpoint</button>
                    @else
                        <button type="button" disabled title="{{ $isFuture ? 'Checkpoint dibuka saat memasuki fase ini' : 'Jendela fase ini sudah lewat' }}" class="cursor-not-allowed rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-semibold tracking-[0.06em] text-slate-500">Isi Checkpoint</button>
                        <p class="mt-2 text-xs text-muted">{{ $isFuture ? 'Terkunci. Dibuka saat program memasuki fase ini.' : 'Ditutup. Jendela fase ini sudah lewat.' }}</p>
                    @endif
                </div>

                @if($isDone)
                {{-- Popup Lihat Checkpoint (riwayat, tidak bisa diubah) --}}
                <div x-show="open" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="open && 'flex'" @click.self="open = false" @keydown.escape.window="open = false" role="dialog" aria-modal="true" aria-label="Riwayat checkpoint minggu {{ $week }}">
                    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl sm:p-8">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">Checkpoint</h2>
                                <p class="mt-0.5 text-sm text-muted">Minggu {{ $week }} · {{ $phaseName }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $reviewPill[1] }}">{{ $reviewPill[0] }}</span>
                                <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div>
                                <dt class="font-semibold">Judul</dt>
                                <dd class="mt-1 rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $formTitle ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Deskripsi Aktivitas</dt>
                                <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-bg/60 px-4 py-2.5">{{ $formDescription ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Bukti Pendukung</dt>
                                <dd class="mt-1 space-y-1.5">
                                    @if($item->evidence_link)
                                        <a href="{{ $item->evidence_link }}" target="_blank" rel="noopener" class="block truncate font-medium text-primary-dark">{{ $item->evidence_link }}</a>
                                    @endif
                                    @if($item->attachment_path)
                                        <a href="{{ asset('storage/'.$item->attachment_path) }}" target="_blank" rel="noopener" class="inline-block font-semibold text-primary-dark">Unduh bukti terlampir</a>
                                    @endif
                                    @unless($item->evidence_link || $item->attachment_path)
                                        <span class="text-muted">—</span>
                                    @endunless
                                </dd>
                            </div>
                            @if($item->mentor_note)
                                <div>
                                    <dt class="font-semibold">Catatan mentor</dt>
                                    <dd class="mt-1 rounded-xl bg-amber-50 px-4 py-2.5 text-amber-800">{{ $item->mentor_note }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
                @elseif($isOpen)
                {{-- Popup Isi Checkpoint --}}
                <div x-show="open" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="open && 'flex'" @click.self="open = false" @keydown.escape.window="open = false" role="dialog" aria-modal="true" aria-label="Isi checkpoint minggu {{ $week }}">
                    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl sm:p-8">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">Checkpoint</h2>
                                <p class="mt-0.5 text-sm text-muted">Minggu {{ $week }} · {{ $phaseName }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $reviewPill[1] }}">{{ $reviewPill[0] }}</span>
                                <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-xl leading-none transition hover:bg-slate-100" aria-label="Tutup">&times;</button>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data" action="{{ route('participant.timeline.update', $item) }}" class="mt-5 space-y-5">
                            @csrf
                            <div>
                                <label for="checkpoint-title-{{ $item->id }}" class="text-sm font-semibold">Judul</label>
                                <input id="checkpoint-title-{{ $item->id }}" name="title" value="{{ old('title', $formTitle) }}" maxlength="180" placeholder="{{ $judulPlaceholder }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400" required>
                            </div>
                            <div>
                                <label for="checkpoint-desc-{{ $item->id }}" class="text-sm font-semibold">Deskripsi Aktivitas</label>
                                <textarea id="checkpoint-desc-{{ $item->id }}" name="description" rows="4" placeholder="{{ $deskripsiPlaceholder }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">{{ old('description', $formDescription) }}</textarea>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Bukti Pendukung</p>
                                <input name="evidence_link" value="{{ old('evidence_link', $item->evidence_link) }}" inputmode="url" placeholder="Link Bukti Pendukung (G-Drive, dll)" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm placeholder:text-slate-400">
                                <div class="mt-3" x-data="{ fileName: '' }">
                                    <input id="checkpoint-file-{{ $item->id }}" type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" class="sr-only" @change="fileName = $event.target.files[0]?.name ?? ''">
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-2.5">
                                        <span class="truncate text-sm text-muted" x-text="fileName || '{{ $item->attachment_path ? 'Sudah ada lampiran (pilih file baru untuk mengganti)' : 'Pilih File Dokumen' }}'">{{ $item->attachment_path ? 'Sudah ada lampiran (pilih file baru untuk mengganti)' : 'Pilih File Dokumen' }}</span>
                                        <label for="checkpoint-file-{{ $item->id }}" class="shrink-0 cursor-pointer rounded-lg bg-mint-light px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary/15">Pilih File</label>
                                    </div>
                                </div>
                                <p class="mt-1.5 text-xs text-muted">Maksimal 10 MB.</p>
                            </div>
                            <div class="flex justify-end">
                                <button class="rounded-xl bg-[#22b07d] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Ajukan Checkpoint</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </article>
        </div>
    @empty
        <x-empty class="mt-6" title="Belum ada checkpoint" />
    @endforelse
</div>
@endunless
@endsection
