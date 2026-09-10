@props(['application'])

@php
    $dosen = $application->participant?->user;
    $mentor = $application->mentor?->user;
@endphp

<article {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-line bg-white shadow-sm']) }}>
    <div class="border-b border-line bg-[#f4f8f6] px-6 py-5 md:px-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-primary-dark">TSU Industry Immersion</p>
                <h2 class="mt-1 text-xl font-semibold">Surat Persetujuan Program Imersi</h2>
                <p class="mt-1 text-sm text-muted">Nomor {{ $application->letter_number ?? 'Menunggu nomor' }}</p>
            </div>
            <x-badge :status="$application->status" />
        </div>
    </div>

    <div class="space-y-5 px-6 py-6 text-sm leading-7 md:px-8">
        <p>Yang bertanda tangan di bawah ini mengajukan dan menyetujui keikutsertaan dosen pada program Industry Immersion dengan data sebagai berikut.</p>

        <dl class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nama dosen</dt>
                <dd class="mt-1 font-medium">{{ $dosen?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">NIP / NIDN</dt>
                <dd class="mt-1 font-medium">{{ $application->participant?->nidn ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Email / Telepon</dt>
                <dd class="mt-1 font-medium">{{ $dosen?->email ?? '—' }} · {{ $dosen?->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Fakultas / Prodi</dt>
                <dd class="mt-1 font-medium">{{ $application->participant?->faculty ?? '—' }} · {{ $application->participant?->study_program ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Kompetensi</dt>
                <dd class="mt-1 font-medium">{{ collect($application->participant?->competency ?? [])->filter()->implode(', ') ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Keahlian</dt>
                <dd class="mt-1 font-medium">{{ collect($application->participant?->expertise ?? [])->filter()->implode(', ') ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Periode</dt>
                <dd class="mt-1 font-medium">{{ $application->periodLabel() }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Curriculum Vitae</dt>
                <dd class="mt-1 font-medium">
                    @if($application->cvUrl())
                        <a href="{{ $application->cvUrl() }}" class="font-semibold text-primary-dark" target="_blank" rel="noopener">Unduh CV</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Unit bisnis</dt>
                <dd class="mt-1 font-medium">{{ $application->department?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</dt>
                <dd class="mt-1 font-medium">{{ $application->businessUnit?->name ?? '—' }}</dd>
            </div>
        </dl>

        <div class="space-y-4">
            @foreach($application->registrationAnswers() as $index => $item)
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Pertanyaan {{ $index + 1 }}</p>
                    <p class="mt-1 font-medium">{{ $item['label'] }}</p>
                    <p class="mt-1">{{ $item['answer'] !== '' ? $item['answer'] : '—' }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-4">
            @foreach([
                ['Dosen', $dosen?->name, $application->created_at],
                ['Admin', 'Pengelola program', $application->admin_reviewed_at],
                ['Mentor', $mentor?->name, $application->mentor_reviewed_at],
                ['Admin final', 'Pengesahan', $application->admin_finalized_at],
            ] as [$role, $name, $at])
                <div class="rounded-xl border border-dashed border-line p-3 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">{{ $role }}</p>
                    <p class="mt-2 font-medium">{{ $name ?: '—' }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $at?->format('d M Y H:i') ?? 'Belum paraf' }}</p>
                </div>
            @endforeach
        </div>

        @if($application->revision_note)
            <p class="rounded-xl bg-amber-50 px-4 py-3 text-amber-800"><b>Catatan revisi:</b> {{ $application->revision_note }}</p>
        @endif
        @if($application->matching_notes)
            <p class="text-muted"><b>Catatan admin:</b> {{ $application->matching_notes }}</p>
        @endif
        @if($application->mentor_note)
            <p class="text-muted"><b>Catatan mentor:</b> {{ $application->mentor_note }}</p>
        @endif
    </div>
</article>
