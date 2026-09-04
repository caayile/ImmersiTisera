@extends('layouts.app')
@section('title', 'Monitoring')
@section('content')
<h1 class="text-2xl font-semibold">Monitoring Program</h1>
<p class="mt-1 text-sm text-muted">Program Manager memantau, bukan menggantikan mentor.</p>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3">Peserta</th>
                <th class="px-4 py-3">Agreement</th>
                <th class="px-4 py-3">Program</th>
                <th class="px-4 py-3">Logbook</th>
                <th class="px-4 py-3">Output</th>
                <th class="px-4 py-3">Evaluasi</th>
                <th class="px-4 py-3">Kolaborasi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $program->agreement?->status }}</td>
                <td class="px-4 py-3"><x-badge :status="$program->status" /></td>
                <td class="px-4 py-3">{{ $program->logbooks->whereIn('status',['approved','reviewed'])->count() }}/{{ $program->logbooks->count() }}</td>
                <td class="px-4 py-3">{{ $program->outputs->where('status','approved')->count() }}/{{ $program->outputs->count() }}</td>
                <td class="px-4 py-3">{{ $program->evaluations->count() }}</td>
                <td class="px-4 py-3">L{{ $program->collaboration?->level ?? 0 }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
