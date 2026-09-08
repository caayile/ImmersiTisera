@extends('layouts.app')
@section('title', 'Mentor')
@section('content')
<h1 class="text-2xl font-semibold">Mentor</h1>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Unit Bisnis</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Posisi</th></tr></thead>
        <tbody>
        @foreach($mentors as $mentor)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $mentor->user->name }}</td>
                <td class="px-4 py-3">{{ $mentor->department?->name }}</td>
                <td class="px-4 py-3">{{ $mentor->businessUnit?->name }}</td>
                <td class="px-4 py-3">{{ $mentor->position }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
