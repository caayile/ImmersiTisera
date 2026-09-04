@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
<h1 class="text-2xl font-semibold">Evaluasi</h1>
@unless($program)
    <x-empty class="mt-6" title="Evaluasi belum dibuka" />
@else
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <form method="POST" class="space-y-4 rounded-2xl border border-line bg-white p-6">
        @csrf
        @foreach(['industry_understanding'=>'Pemahaman Industri','relationship'=>'Relasi','output'=>'Hasil','mutual_benefit'=>'Manfaat Bersama','collaboration_potential'=>'Potensi Kolaborasi'] as $name => $label)
            <label class="block text-sm">{{ $label }}
                <input type="number" min="1" max="5" name="{{ $name }}" value="{{ old($name, $mine?->$name ?? 4) }}" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
            </label>
        @endforeach
        <textarea name="comments" rows="3" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" placeholder="Catatan">{{ old('comments', $mine?->comments) }}</textarea>
        <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Simpan evaluasi</button>
    </form>
    <article class="rounded-2xl border border-line bg-white p-6">
        <h2 class="font-semibold">Hasil ringkas</h2>
        @forelse($program->evaluations as $eval)
            <p class="mt-3 text-sm"><b>{{ $eval->evaluator->name }}</b> · rata-rata {{ $eval->average() }} / 5</p>
        @empty
            <p class="mt-3 text-sm text-muted">Belum ada evaluasi.</p>
        @endforelse
    </article>
</div>
@endif
@endsection
