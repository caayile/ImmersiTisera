@extends('layouts.app')
@section('title', 'Penilaian')
@section('content')
<p class="text-sm text-muted">Buat nilai raport peserta dan sahkan sekaligus terbitkan sertifikat dalam satu alur.</p>
<div class="mt-6">
    <div class="relative">
        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
        <input id="eval-search" type="search" placeholder="Cari nama..." class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
    </div>

    <div>
        <p class="mt-4 text-sm text-muted">Pilih peserta, lalu isi nilai magang dan tanda tangan. Setelah keduanya lengkap, terbitkan penilaian sekaligus sertifikat dari halaman peserta.</p>
        <div class="mt-4 space-y-4">
            @forelse($programs as $program)
                @php
                    $participantUser = $program->participant?->user;
                    $programCertificate = ($certificates ?? collect())->get($program->id);
                    $programAvg = ($given ?? collect())->keyBy('program_id')->get($program->id)?->reportAverage();
                @endphp
                <article class="eval-row rounded-2xl border border-line bg-white p-5" data-search="{{ strtolower(($participantUser?->name ?: '').' '.($participantUser?->email ?: '').' '.($program->businessUnit?->name ?: '')) }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-semibold">{{ $participantUser?->name ?: 'Peserta' }}</h3>
                            <p class="mt-0.5 truncate text-sm text-muted">{{ $participantUser?->email }}</p>
                            <p class="mt-0.5 text-sm text-primary-dark">{{ $program->businessUnit?->name }}</p>
                            <p class="mt-0.5 text-xs text-muted">Status: {{ $program->status }} · Sejak {{ $program->created_at?->format('d M Y') }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                            @if($programCertificate?->isIssued())
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                    <span class="material-symbols-outlined text-[14px]">verified</span>
                                    Raport disahkan · {{ $programCertificate->number }}
                                </span>
                            @elseif($programAvg === null)
                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-800">Belum dinilai</span>
                            @else
                                <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-sky-700">Nilai {{ $programAvg }} · menunggu terbit</span>
                            @endif
                            <a href="{{ route('mentor.penilaian.show', $program) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                                <span class="material-symbols-outlined text-[18px]">grade</span>
                                Penilaian
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white px-4 py-8 text-center text-sm text-muted">Belum ada peserta bimbingan.</p>
            @endforelse
        </div>
    </div>
    <p id="eval-empty" class="mt-4 hidden text-center text-sm text-muted">Tidak ada penilaian yang cocok dengan pencarian.</p>
</div>

<script>
(function () {
    var search = document.getElementById('eval-search');
    var emptyMsg = document.getElementById('eval-empty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.eval-row'));

    function render() {
        var query = search ? search.value.trim().toLowerCase() : '';
        var visible = 0;
        rows.forEach(function (row) {
            var show = !query || row.dataset.search.indexOf(query) !== -1;
            row.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });
        if (emptyMsg) emptyMsg.classList.toggle('hidden', visible > 0);
    }

    if (search) search.addEventListener('input', render);
})();
</script>
@endsection
