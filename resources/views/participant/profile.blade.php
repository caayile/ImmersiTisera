@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
@endphp

<div
    class="mx-auto max-w-5xl"
    x-data="{
        catalog: @js($studyProgramCatalog),
        placementCatalog: @js($placementCatalog),
        faculty: @js(old('faculty', $participant->faculty)),
        study_program: @js(old('study_program', $participant->study_program)),
        get programs() {
            return this.catalog[this.faculty] || [];
        },
        get targets() {
            return this.placementCatalog[this.study_program] || [];
        },
        syncProgram() {
            if (! this.programs.includes(this.study_program)) {
                this.study_program = '';
            }
        },
        selectFaculty(name) {
            this.faculty = name;
            this.syncProgram();
        }
    }"
>
    <div class="mb-4 flex flex-wrap items-center gap-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke beranda
        </a>
        <a href="{{ route('profile.public') }}" class="text-sm font-semibold text-muted transition hover:text-ink">Kembali ke profil</a>
    </div>

    <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="hero-grid px-6 py-8 md:px-10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl font-semibold text-primary-dark shadow-sm">{{ $initials }}</span>
                <div class="text-white">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Profil dosen · Imersi</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-white/75">Lengkapi data akademik agar pencocokan unit bisnis lebih akurat.</p>
                </div>
                <a href="{{ route('profile.public') }}" class="inline-flex items-center gap-1 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-ink sm:ml-auto">
                    Lihat profil publik
                    <span class="material-symbols-outlined text-[18px]">arrow_outward</span>
                </a>
            </div>
        </div>

        <form method="POST" class="space-y-8 p-6 md:p-10">
            @csrf

            <section>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                        <span class="material-symbols-outlined text-[22px]">badge</span>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold">Identitas</h2>
                        <p class="mt-1 text-sm text-muted">Data dasar yang tampil di dasbor dan profil publik.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="md:col-span-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nama lengkap</span>
                        <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Telepon</span>
                        <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">NIP / NIDN</span>
                        <input name="nidn" value="{{ old('nidn', $participant->nidn) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border border-line bg-[#f4f8f6] p-5 md:p-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-primary-dark shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">school</span>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold">Fakultas dan program studi</h2>
                        <p class="mt-1 text-sm text-muted">Pilih dari daftar resmi — tanpa mengetik manual.</p>
                    </div>
                </div>

                <input type="hidden" name="faculty" :value="faculty">
                <input type="hidden" name="study_program" :value="study_program">

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @foreach($studyProgramCatalog as $facultyName => $programs)
                        <button
                            type="button"
                            @click="selectFaculty(@js($facultyName))"
                            class="rounded-2xl border px-4 py-4 text-left transition"
                            :class="faculty === @js($facultyName) ? 'border-primary bg-white shadow-sm ring-4 ring-primary/15' : 'border-line bg-white/70 hover:border-primary/40'"
                        >
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/15 text-primary-dark">
                                <span class="material-symbols-outlined text-[18px]">
                                    {{ str_contains($facultyName, 'Vokasi') ? 'engineering' : (str_contains($facultyName, 'Humaniora') ? 'diversity_3' : 'memory') }}
                                </span>
                            </span>
                            <p class="mt-3 text-sm font-semibold">{{ $facultyName }}</p>
                            <p class="mt-1 text-xs text-muted">{{ count($programs) }} program studi</p>
                        </button>
                    @endforeach
                </div>
                @error('faculty')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="mt-5" x-show="faculty" x-cloak>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Program studi</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="program in programs" :key="program">
                            <button
                                type="button"
                                @click="study_program = program"
                                class="rounded-full px-4 py-2 text-sm font-medium transition"
                                :class="study_program === program ? 'bg-primary text-white shadow-sm' : 'bg-white text-ink ring-1 ring-line hover:ring-primary/40'"
                                x-text="program"
                            ></button>
                        </template>
                    </div>
                </div>
                @error('study_program')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="mt-5 rounded-2xl border border-primary/20 bg-white p-4" x-show="targets.length" x-cloak>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-dark">place</span>
                        <p class="text-sm font-semibold">Rekomendasi penempatan</p>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <template x-for="target in targets" :key="target.unit">
                            <div class="rounded-xl bg-bg px-4 py-3">
                                <p class="font-medium" x-text="target.unit"></p>
                                <p class="mt-1 text-xs text-muted" x-text="'Tujuan: ' + target.departments.join(', ')"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                        <span class="material-symbols-outlined text-[22px]">psychology</span>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold">Kompetensi dan keahlian</h2>
                        <p class="mt-1 text-sm text-muted">Dipakai mesin pencocokan ke unit bisnis mitra.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4">
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Kompetensi <span class="normal-case tracking-normal text-muted/80">(pisahkan dengan koma)</span></span>
                        <input name="competency" value="{{ old('competency', implode(', ', $participant->competency ?? [])) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Expertise <span class="normal-case tracking-normal text-muted/80">(pisahkan dengan koma)</span></span>
                        <input name="expertise" value="{{ old('expertise', implode(', ', $participant->expertise ?? [])) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
                    </label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label>
                            <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Pengalaman relevan</span>
                            <textarea name="experience" rows="4" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">{{ old('experience', $participant->experience) }}</textarea>
                        </label>
                        <label>
                            <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Motivasi</span>
                            <textarea name="motivation" rows="4" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">{{ old('motivation', $participant->motivation) }}</textarea>
                        </label>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Kembali ke beranda
                    </a>
                    <p class="text-sm text-muted">Perubahan langsung memengaruhi skor pencocokan program.</p>
                </div>
                <button class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan profil
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
