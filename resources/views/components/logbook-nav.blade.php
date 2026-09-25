@props(['active' => 'logbook'])

<div {{ $attributes->merge(['class' => 'grid gap-4 sm:grid-cols-2']) }}>
    <a href="{{ route('participant.logbooks') }}" class="rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md {{ $active === 'logbook' ? 'border-primary ring-1 ring-primary/30' : 'border-line' }}">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
            <span class="material-symbols-outlined text-[22px]">edit_note</span>
        </span>
        <p class="mt-4 font-semibold">Logbook</p>
        <p class="mt-1 text-sm text-muted">Isi dan kelola refleksi harian program magang dosen Anda.</p>
    </a>
    <a href="{{ route('participant.logbooks.history') }}" class="rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md {{ $active === 'history' ? 'border-primary ring-1 ring-primary/30' : 'border-line' }}">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
            <span class="material-symbols-outlined text-[22px]">history_edu</span>
        </span>
        <p class="mt-4 font-semibold">Riwayat Logbook</p>
        <p class="mt-1 text-sm text-muted">Daftar periode magang dan entri logbook per tahun.</p>
    </a>
</div>
