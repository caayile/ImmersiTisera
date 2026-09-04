@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
<h1 class="text-2xl font-semibold">Evaluasi Peserta</h1>
@foreach($programs as $program)
    @php $mine = $program->evaluations->firstWhere('evaluator_id', auth()->id()); @endphp
    <form method="POST" action="{{ route('mentor.evaluations.store', $program) }}" class="mt-6 rounded-2xl border border-line bg-white p-5">
        @csrf
        <p class="font-medium">{{ $program->participant->user->name }}</p>
        <div class="mt-3 grid gap-3 md:grid-cols-5">
            @foreach(['industry_understanding','relationship','output','mutual_benefit','collaboration_potential'] as $field)
                <label class="text-xs">{{ str_replace('_',' ', $field) }}
                    <input type="number" min="1" max="5" name="{{ $field }}" value="{{ $mine?->$field ?? 4 }}" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                </label>
            @endforeach
        </div>
        <textarea name="comments" class="mt-3 w-full rounded-lg border border-line px-3 py-2 text-sm" placeholder="Catatan">{{ $mine?->comments }}</textarea>
        <button class="mt-3 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
    </form>
@endforeach
@endsection
