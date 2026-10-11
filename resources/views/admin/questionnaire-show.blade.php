@extends('layouts.app')
@section('title', $section->title)
@section('content')
<a href="{{ route('admin.questionnaires') }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke bank kuesioner</a>
<p class="mt-1 text-sm text-muted">Jumlah pertanyaan bebas — tambah atau hapus sesuai kebutuhan.</p>

<form method="POST" action="{{ route('admin.questionnaires.save', $section) }}" class="mt-6 rounded-2xl border border-line bg-white p-5">
    @csrf
    <div class="grid gap-3 sm:grid-cols-[200px_1fr]">
        <div>
            <label class="block text-sm font-semibold">Tentang</label>
            <input name="key" value="{{ old('key', $section->key) }}" maxlength="50" pattern="[A-Za-z0-9_-]+" class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm uppercase" required>
        </div>
        <div>
            <label class="block text-sm font-semibold">Judul Bagian</label>
            <input name="title" value="{{ old('title', $section->title) }}" maxlength="255" class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm" required>
        </div>
    </div>

    <p class="mt-5 text-sm font-semibold">Pertanyaan</p>
    <div class="mt-2 space-y-2">
        @forelse($section->questions as $index => $item)
            <div class="grid items-start gap-2 rounded-xl border border-line p-3 md:grid-cols-[32px_1fr_140px_auto]">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-bg text-sm font-semibold text-muted">{{ $index + 1 }}</span>
                <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $item->id }}">
                <textarea name="questions[{{ $index }}][question]" rows="2" maxlength="1000" class="w-full rounded-lg border border-line px-3 py-2 text-sm" required>{{ $item->question }}</textarea>
                <select name="questions[{{ $index }}][is_active]" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="1" @selected($item->is_active)>Aktif</option>
                    <option value="0" @selected(! $item->is_active)>Nonaktif</option>
                </select>
                <button type="submit" name="delete_id" value="{{ $item->id }}" onclick="return confirm('Hapus pertanyaan ini?')" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700">Hapus</button>
            </div>
        @empty
            <p class="text-sm text-muted">Belum ada pertanyaan di bagian ini.</p>
        @endforelse
    </div>

    <label class="mt-4 block text-sm font-semibold">Tambah Pertanyaan Baru</label>
    <textarea name="new_question" rows="2" maxlength="1000" placeholder="Tulis pertanyaan baru... (kosongkan bila tidak menambah)" class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm placeholder:text-slate-400"></textarea>

    <div class="mt-4 flex justify-end">
        <button type="submit" name="save" value="1" class="rounded-xl bg-[#22b07d] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Simpan</button>
    </div>
</form>
@endsection
