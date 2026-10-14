@extends('layouts.app')
@section('title', $section->title)
@section('content')
@php
    $initialRows = $section->questions->values()->map(fn ($item) => [
        'uid' => 'q-'.$item->id,
        'id' => $item->id,
        'question' => $item->question,
    ])->all();
@endphp

<a href="{{ route('admin.questionnaires') }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke bank kuesioner</a>
<p class="mt-1 text-sm text-muted">Tambah, ubah, atau hapus pertanyaan, lalu simpan.</p>

<script>
    window.questionnaireEditor = function (initialRows) {
        return {
            rows: Array.isArray(initialRows) ? initialRows : [],
            nextUid: 1,
            addRow() {
                this.rows.push({ uid: 'new-' + this.nextUid++, id: null, question: '' });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
        };
    };
</script>

<form method="POST" action="{{ route('admin.questionnaires.save', $section) }}" class="mt-6 rounded-2xl border border-line bg-white p-5" x-data="questionnaireEditor(@js($initialRows))">
    @csrf
    <div class="grid gap-3 sm:grid-cols-[180px_1fr]">
        <div>
            <label class="block text-sm font-semibold">Tentang</label>
            <input name="key" value="{{ old('key', $section->key) }}" maxlength="50" pattern="[A-Za-z0-9_-]+" class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm uppercase" required>
        </div>
        <div>
            <label class="block text-sm font-semibold">Judul Bagian</label>
            <input name="title" value="{{ old('title', $section->title) }}" maxlength="255" class="mt-2 w-full rounded-xl border border-line px-4 py-2.5 text-sm" required>
        </div>
    </div>

    <div class="mt-5 flex items-center justify-between gap-3">
        <p class="text-sm font-semibold">Pertanyaan</p>
        <button type="button" @click="addRow()" class="inline-flex items-center gap-1.5 rounded-xl border border-primary/40 px-3.5 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah pertanyaan
        </button>
    </div>

    <div class="mt-2 space-y-2">
        <template x-for="(row, index) in rows" :key="row.uid">
            <div class="flex items-start gap-2 rounded-xl border border-line p-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-bg text-sm font-semibold text-muted" x-text="index + 1"></span>
                <input type="hidden" :name="'questions[' + index + '][id]'" :value="row.id || ''">
                <textarea :name="'questions[' + index + '][question]'" x-model="row.question" rows="2" maxlength="1000" class="min-w-0 flex-1 rounded-lg border border-line px-3 py-2 text-sm" required placeholder="Tulis pertanyaan..."></textarea>
                <button type="button" @click="removeRow(index)" class="shrink-0 rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">Hapus</button>
            </div>
        </template>
        <p x-show="rows.length === 0" class="rounded-xl border border-dashed border-line px-4 py-6 text-center text-sm text-muted">Belum ada pertanyaan. Klik “Tambah pertanyaan”.</p>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit" name="save" value="1" class="rounded-xl bg-[#22b07d] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a9a6d]">Simpan</button>
    </div>
</form>
@endsection
