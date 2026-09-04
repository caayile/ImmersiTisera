@extends('layouts.app')
@section('title', 'Evaluasi')
@section('content')
<h1 class="text-2xl font-semibold">Evaluation</h1>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Evaluator</th><th class="px-4 py-3">Rata-rata</th></tr></thead>
        <tbody>
        @foreach($evaluations as $evaluation)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $evaluation->program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $evaluation->evaluator->name }}</td>
                <td class="px-4 py-3">{{ $evaluation->average() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
