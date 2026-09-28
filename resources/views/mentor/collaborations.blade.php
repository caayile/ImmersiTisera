@extends('layouts.app')
@section('title', 'Kolaborasi')
@section('content')
<h1 class="text-2xl font-semibold">Rencana Kolaborasi</h1>
@foreach($programs as $program)
    @php $item = $program->collaboration; @endphp
    <form method="POST" action="{{ route('mentor.collaborations.update', $program) }}" class="mt-6 space-y-3 rounded-2xl border border-line bg-white p-5">
        @csrf
        <p class="font-medium">{{ $program->participant->user->name }}</p>
        <select name="level" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
            @foreach($levels as $value => $label)
                <option value="{{ $value }}" @selected(($item->level ?? 0) == $value)>{{ \App\Support\Status::collaborationLevelLabel((int) $value) }}</option>
            @endforeach
        </select>
        <select name="collaboration_type" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
            @foreach($types as $type)<option value="{{ $type }}" @selected($item?->collaboration_type === $type)>{{ \App\Support\Status::collaborationTypeLabel($type) }}</option>@endforeach
        </select>
        <textarea name="description" class="w-full rounded-lg border border-line px-3 py-2 text-sm" placeholder="Deskripsi">{{ $item?->description }}</textarea>
        <input name="next_action" value="{{ $item?->next_action }}" placeholder="Tindak lanjut" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
        <input name="responsible_person" value="{{ $item?->responsible_person }}" placeholder="Penanggung jawab" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
        <input type="date" name="target_date" value="{{ $item?->target_date?->toDateString() }}" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
        <textarea name="notes" class="w-full rounded-lg border border-line px-3 py-2 text-sm" placeholder="Catatan">{{ $item?->notes }}</textarea>
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan rencana</button>
    </form>
@endforeach
@endsection
