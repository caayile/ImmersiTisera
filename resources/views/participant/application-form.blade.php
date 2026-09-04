@extends('layouts.app')
@section('title', 'Ajukan Program')
@section('content')
<h1 class="text-2xl font-semibold">Ajukan Program</h1>
<p class="mt-1 text-sm text-muted">Pilih departemen atau unit bisnis yang relevan dengan kompetensi Anda.</p>
<form method="POST" action="{{ route('participant.applications.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Departemen / penempatan
        <select name="business_unit_id" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
            @foreach($departments as $department)
                @if($department->isDirectPlacement())
                    @php $unit = $department->businessUnits->first(); @endphp
                    @if($unit)
                        <option value="{{ $unit->id }}" @selected(old('business_unit_id', $prefill) == $unit->id)>
                            {{ $department->area ? $department->area.' · ' : '' }}{{ $department->name }}
                        </option>
                    @endif
                @else
                    <optgroup label="{{ $department->name }}">
                        @foreach($department->businessUnits as $unit)
                            <option value="{{ $unit->id }}" @selected(old('business_unit_id', $prefill) == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </optgroup>
                @endif
            @endforeach
        </select>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Periode yang diinginkan
        <input name="preferred_period" value="{{ old('preferred_period', '8 weeks / 60 days') }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Motivasi
        <textarea name="motivation" rows="5" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('motivation') }}</textarea>
    </label>
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Kirim pengajuan</button>
</form>
@endsection
