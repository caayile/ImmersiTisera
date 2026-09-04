@extends('layouts.app')
@section('title', 'Agreement')
@section('content')
<h1 class="text-2xl font-semibold">Agreement</h1>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Output</th></tr></thead>
        <tbody>
        @foreach($agreements as $agreement)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $agreement->program->participant->user->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$agreement->status" /></td>
                <td class="px-4 py-3">{{ $agreement->main_output }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
