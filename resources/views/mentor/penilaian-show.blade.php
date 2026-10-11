@extends('layouts.app')
@section('title', 'Penilaian Peserta')
@section('content')
@php
    $participantUser = $program->participant?->user;
    $issued = $certificate?->isIssued();
    $gradeError = collect($errors->getMessages())->except('certificate_signature')->flatten()->first();
@endphp
<a href="{{ route('mentor.penilaian') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-dark hover:underline">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    Kembali ke daftar penilaian
</a>

<article class="mt-4 rounded-2xl border border-line bg-white p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-lg font-bold">{{ $participantUser?->name ?: 'Peserta' }}</h2>
            <p class="mt-0.5 truncate text-sm text-muted">{{ $participantUser?->email }}</p>
            <p class="mt-0.5 text-sm text-primary-dark">{{ $program->businessUnit?->name }}{{ $program->department?->name ? ' · '.$program->department->name : '' }}</p>
            <p class="mt-0.5 text-xs text-muted">Status: {{ $program->status }} · Sejak {{ $program->created_at?->format('d M Y') }}</p>
        </div>
        <div class="shrink-0 sm:text-right">
            @if($average === null)
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-800">Belum dinilai</span>
            @else
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Rata-rata nilai magang</p>
                <p class="mt-1 text-3xl font-semibold">{{ $average }} <span class="text-lg">{{ $evaluation->predicate() }}</span></p>
            @endif
            @if($issued)
                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                    <span class="material-symbols-outlined text-[14px]">verified</span>
                    Raport disahkan · {{ $certificate->number }}
                </p>
            @endif
        </div>
    </div>
</article>

<div class="mt-6" x-data="{
    groups: @js($groups),
    comments: @js($comments),
    avg() {
        let weighted = 0, total = 0;
        for (const group of this.groups) {
            const scores = (group.aspects || []).map((a) => parseFloat(a.score)).filter((v) => !Number.isNaN(v));
            if (!scores.length) continue;
            const weight = parseFloat(group.weight) || 0;
            weighted += (scores.reduce((a, b) => a + b, 0) / scores.length) * weight;
            total += weight;
        }
        return total > 0 ? Math.round((weighted / total) * 10) / 10 : null;
    },
    predicate(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (value >= 85) return 'A';
        if (value >= 70) return 'B';
        if (value >= 60) return 'C';
        if (value >= 50) return 'D';
        return 'E';
    },
    weightTotal() {
        return this.groups.reduce((sum, g) => sum + (parseFloat(g.weight) || 0), 0);
    },
    addAspect(gi) {
        const aspects = this.groups[gi].aspects;
        if (aspects.length < 20) aspects.push({ label: '', score: null });
    },
    removeAspect(gi, ai) {
        const aspects = this.groups[gi].aspects;
        if (aspects.length > 1) aspects.splice(ai, 1);
    },
    addGroup() {
        if (this.groups.length < 10) this.groups.push({ name: '', weight: 0, aspects: [{ label: '', score: null }] });
    },
    removeGroup(gi) {
        if (this.groups.length > 1) this.groups.splice(gi, 1);
    }
}">
    <article class="rounded-2xl border border-line bg-white p-5">
        <h3 class="text-lg font-bold">Input Nilai Magang</h3>
        <p class="mt-1 text-xs text-muted">Nilai 0–100 untuk setiap aspek. Rata-rata dihitung otomatis dari bobot kelompok.</p>
        @if($gradeError)
            <p class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">{{ $gradeError }}</p>
        @endif
        <form method="POST" action="{{ route('mentor.evaluations.store', $program) }}" class="mt-5 space-y-5">
            @csrf
            <template x-for="(group, gi) in groups" :key="gi">
                <div class="rounded-2xl border border-line p-4">
                    <div class="grid gap-3 sm:grid-cols-[1fr_8rem_auto] sm:items-end">
                        <div>
                            <label class="text-xs font-medium text-muted" :for="`rg-name-${gi}`">Kelompok (bobot <span x-text="group.weight"></span>%)</label>
                            <input :id="`rg-name-${gi}`" :name="`groups[${gi}][name]`" x-model="group.name" maxlength="120" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm font-semibold outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted" :for="`rg-weight-${gi}`">Bobot (%)</label>
                            <input :id="`rg-weight-${gi}`" :name="`groups[${gi}][weight]`" x-model="group.weight" type="number" min="0" max="100" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                        </div>
                        <button type="button" @click="removeGroup(gi)" :disabled="groups.length === 1" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-white text-muted transition hover:border-red-300 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40" aria-label="Hapus kelompok">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                    <div class="mt-3 space-y-2">
                        <template x-for="(aspect, ai) in group.aspects" :key="ai">
                            <div class="grid gap-3 sm:grid-cols-[1fr_7rem_auto] sm:items-end">
                                <input :name="`groups[${gi}][aspects][${ai}][label]`" x-model="aspect.label" maxlength="120" placeholder="Nama aspek" class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                                <input :name="`groups[${gi}][aspects][${ai}][score]`" x-model="aspect.score" type="number" min="0" max="100" placeholder="Nilai" class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                                <button type="button" @click="removeAspect(gi, ai)" :disabled="group.aspects.length === 1" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-white text-xs font-semibold text-red-600 transition hover:border-red-300 disabled:cursor-not-allowed disabled:opacity-40">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addAspect(gi)" class="mt-3 inline-flex items-center gap-1.5 rounded-xl border border-primary/30 px-3 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Tambah aspek
                    </button>
                </div>
            </template>
            <button type="button" @click="addGroup()" class="inline-flex items-center gap-1.5 rounded-xl border border-line px-3 py-2 text-sm font-semibold text-muted transition hover:border-primary hover:text-primary-dark">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah kelompok
            </button>
            <div class="grid gap-3 rounded-2xl bg-[#eef6f2] p-4 sm:grid-cols-[auto_1fr] sm:items-center">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Rata-rata nilai magang (otomatis)</p>
                    <p class="mt-1 text-3xl font-semibold"><span x-text="avg() ?? '—'"></span> <span class="text-lg" x-text="predicate(avg())"></span></p>
                    <p class="mt-1 text-xs text-muted">Rumus: Σ(rata-rata kelompok × bobot) ÷ total bobot (<span x-text="weightTotal()"></span>%)</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted">Catatan (opsional)</label>
                    <textarea name="comments" rows="3" x-model="comments" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Catatan untuk peserta"></textarea>
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan nilai</button>
            </div>
        </form>
    </article>
</div>

<article id="tanda-tangan" class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h3 class="text-lg font-bold">Tanda tangan sertifikat</h3>
    <p class="mt-1 text-sm text-muted">Tanda tangan ini dipakai otomatis saat menerbitkan sertifikat {{ $participantUser?->name ?: 'peserta ini' }}.</p>
    @if(filled($mentor?->certificate_signature))
        <div class="mt-4 rounded-xl border border-line bg-bg/50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Tanda tangan tersimpan</p>
            <img src="{{ $mentor->certificate_signature }}" alt="Tanda tangan mentor" class="mt-3 h-20 object-contain">
        </div>
    @endif
    <form method="POST" action="{{ route('mentor.certificates.signature') }}" class="mt-4 space-y-3">
        @csrf
        <x-signature-pad
            name="certificate_signature"
            label="Gambar ulang tanda tangan"
            hint="Wajib diisi untuk menyimpan atau memperbarui tanda tangan."
            :required="true"
            :value="old('certificate_signature')"
        />
        @if ($errors->has('certificate_signature'))
            <p class="text-sm text-red-600">{{ $errors->first('certificate_signature') }}</p>
        @endif
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">{{ filled($mentor?->certificate_signature) ? 'Perbarui tanda tangan' : 'Simpan tanda tangan' }}</button>
    </form>
</article>

<article class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h3 class="text-lg font-bold">Terbitkan penilaian & sertifikat</h3>
    <p class="mt-1 text-sm text-muted">Pengesahan menerbitkan nilai raport sekaligus sertifikat secara bersama.</p>
    <ul class="mt-4 space-y-2 text-sm">
        <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] {{ $average === null ? 'text-muted' : 'text-emerald-600' }}">{{ $average === null ? 'pending' : 'check_circle' }}</span>
            <span>Nilai magang {{ $average === null ? 'belum diisi' : 'sudah diisi ('.$average.' '.$evaluation->predicate().')' }}</span>
        </li>
        <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] {{ $hasSignature ? 'text-emerald-600' : 'text-muted' }}">{{ $hasSignature ? 'check_circle' : 'pending' }}</span>
            <span>Tanda tangan sertifikat {{ $hasSignature ? 'sudah tersimpan' : 'belum diisi' }}</span>
        </li>
    </ul>
    <div class="mt-4">
        @if($issued)
            <p class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                Raport disahkan · {{ $certificate->number }}
            </p>
            <div class="mt-3">
                <a href="{{ route('mentor.certificates.print', $certificate) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-line px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:border-primary">
                    <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    Lihat sertifikat
                </a>
            </div>
        @elseif($average === null || ! $hasSignature)
            <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-xl bg-bg px-4 py-2.5 text-sm font-semibold text-muted">
                <span class="material-symbols-outlined text-[18px]">lock</span>
                Sahkan Raport & Terbitkan Sertifikat
            </button>
            <p class="mt-2 text-xs text-muted">
                @if($average === null)
                    Lengkapi nilai magang terlebih dahulu.
                @else
                    Simpan tanda tangan sertifikat di atas untuk menerbitkan.
                @endif
            </p>
        @else
            <form method="POST" action="{{ route('mentor.evaluations.approve', $program) }}" onsubmit="return confirm('Sahkan raport sekaligus terbitkan sertifikat?')">
                @csrf
                <button class="inline-flex items-center gap-1.5 rounded-xl bg-[#22b07d] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    Sahkan Raport & Terbitkan Sertifikat
                </button>
            </form>
        @endif
    </div>
</article>
@endsection
