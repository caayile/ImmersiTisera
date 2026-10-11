@extends('layouts.app')
@section('title', 'Rencana Kolaborasi')
@section('content')
<div class="overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Tahap</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Tindak lanjut</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $item->program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ \App\Support\Status::collaborationLevelLabel($item->level) }}</td>
                <td class="px-4 py-3">{{ \App\Support\Status::collaborationTypeLabel($item->collaboration_type) }}</td>
                <td class="px-4 py-3">{{ $item->next_action ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
