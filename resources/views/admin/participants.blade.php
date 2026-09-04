@extends('layouts.app')
@section('title', 'Peserta')
@section('content')
<h1 class="text-2xl font-semibold">Peserta</h1>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Prodi</th><th class="px-4 py-3">Kompetensi</th></tr></thead>
        <tbody>
        @foreach($participants as $participant)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $participant->user->name }}</td>
                <td class="px-4 py-3">{{ $participant->study_program }}</td>
                <td class="px-4 py-3">{{ implode(', ', $participant->competency ?? []) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
