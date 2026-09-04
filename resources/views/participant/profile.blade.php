@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
<h1 class="text-2xl font-semibold">Profil Saya</h1>
<p class="mt-1 text-sm text-muted">Matching memakai prodi, kompetensi, dan expertise Anda.</p>
<form method="POST" class="mt-6 grid max-w-3xl gap-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Nama
        <input name="name" value="{{ old('name', auth()->user()->name) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-muted">Telepon
            <input name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
        </label>
        <label class="text-xs font-semibold uppercase tracking-wide text-muted">NIP / NIDN
            <input name="nidn" value="{{ old('nidn', $participant->nidn) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
        </label>
    </div>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Program Studi
        <input name="study_program" value="{{ old('study_program', $participant->study_program) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Kompetensi (pisahkan koma)
        <input name="competency" value="{{ old('competency', implode(', ', $participant->competency ?? [])) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Expertise (pisahkan koma)
        <input name="expertise" value="{{ old('expertise', implode(', ', $participant->expertise ?? [])) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Pengalaman relevan
        <textarea name="experience" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('experience', $participant->experience) }}</textarea>
    </label>
    <label class="text-xs font-semibold uppercase tracking-wide text-muted">Motivasi
        <textarea name="motivation" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('motivation', $participant->motivation) }}</textarea>
    </label>
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Simpan profil</button>
</form>
@endsection
