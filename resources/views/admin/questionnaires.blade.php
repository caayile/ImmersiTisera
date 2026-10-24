@extends('layouts.app')
@section('title', 'Bank Kuesioner Evaluasi')
@section('content')
<p class="text-sm text-muted">Kelola bagian kuesioner, pernyataan, dan skala penilaian dosen di menu Hasil Studi.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
    <p class="border-b border-line px-5 py-3 text-sm font-semibold">Bagian Kuesioner</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-sm">
            <thead>
                <tr class="border-b border-line text-muted">
                    <th class="w-12 px-5 py-3 text-left font-medium">No</th>
                    <th class="px-5 py-3 text-left font-medium">Tentang</th>
                    <th class="px-5 py-3 text-left font-medium">Judul Bagian</th>
                    <th class="px-5 py-3 text-left font-medium">Pernyataan</th>
                    <th class="w-36 px-5 py-3 text-left font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                    <tr class="border-b border-line last:border-0">
                        <td class="px-5 py-3 text-muted">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3 font-semibold uppercase">{{ $section->key }}</td>
                        <td class="px-5 py-3">{{ $section->title }}</td>
                        <td class="px-5 py-3">{{ $section->questions_count }} pernyataan</td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.questionnaires.show', $section) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#22b07d] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#1a9a6d]">
                                    Kelola
                                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                                </a>
                                <form method="POST" action="{{ route('admin.questionnaires.destroy', $section) }}" onsubmit="return confirm('Hapus bagian kuesioner ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-xl border border-red-200 px-4 py-2 text-xs font-semibold text-red-700">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-muted">Belum ada bagian kuesioner.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('admin.questionnaires.store') }}" class="flex flex-col gap-2 border-t border-line p-4 sm:flex-row">
        @csrf
        <input name="key" maxlength="50" placeholder="Key... (cth. PROGRAM)" class="w-44 rounded-lg border border-line px-3 py-2 text-sm" required>
        <input name="title" maxlength="255" placeholder="Judul Bagian... (cth. Evaluasi terhadap Program)" class="flex-1 rounded-lg border border-line px-3 py-2 text-sm" required>
        <button class="shrink-0 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah</button>
    </form>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
    <p class="border-b border-line px-5 py-3 text-sm font-semibold">Skala Penilaian</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-sm">
            <thead>
                <tr class="border-b border-line text-muted">
                    <th class="w-12 px-5 py-3 text-left font-medium">No</th>
                    <th class="px-5 py-3 text-left font-medium">Nama Skala</th>
                    <th class="w-28 px-5 py-3 text-left font-medium">Nilai</th>
                    <th class="w-44 px-5 py-3 text-left font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scales as $scale)
                    <tr class="border-b border-line last:border-0">
                        <td class="px-5 py-2 text-muted">{{ $loop->iteration }}</td>
                        <td class="px-5 py-2">
                            <form id="scale-update-{{ $scale->id }}" method="POST" action="{{ route('admin.scales.update', $scale) }}" class="flex gap-2">
                                @csrf
                                @method('PUT')
                                <input name="label" value="{{ $scale->label }}" maxlength="50" class="w-full rounded-lg border border-line px-3 py-1.5 text-sm" required>
                                <input name="value" type="number" min="1" max="10" value="{{ $scale->value }}" class="w-20 shrink-0 rounded-lg border border-line px-3 py-1.5 text-sm" required>
                            </form>
                        </td>
                        <td class="px-5 py-2 font-semibold">{{ $scale->value }}</td>
                        <td class="px-5 py-2">
                            <div class="flex gap-2">
                                <button form="scale-update-{{ $scale->id }}" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white">Simpan</button>
                                <form method="POST" action="{{ route('admin.scales.destroy', $scale) }}" onsubmit="return confirm('Hapus skala ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-muted">Belum ada skala penilaian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('admin.scales.store') }}" class="flex flex-col gap-2 border-t border-line p-4 sm:flex-row">
        @csrf
        <input name="label" maxlength="50" placeholder="Nama skala baru... (cth. Sangat Setuju)" class="flex-1 rounded-lg border border-line px-3 py-2 text-sm" required>
        <input name="value" type="number" min="1" max="10" placeholder="Nilai" class="w-28 rounded-lg border border-line px-3 py-2 text-sm" required>
        <button class="shrink-0 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah</button>
    </form>
</div>
@endsection
