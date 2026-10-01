@props(['criteria' => []])

@php
    $defaults = [
        ['label' => 'Pemahaman Industri', 'score' => ''],
        ['label' => 'Relasi', 'score' => ''],
        ['label' => 'Hasil', 'score' => ''],
        ['label' => 'Manfaat Bersama', 'score' => ''],
        ['label' => 'Potensi Kolaborasi', 'score' => ''],
    ];
    $initialCriteria = old('criteria', $criteria ?: $defaults);
@endphp

<div class="space-y-3" x-data="{ criteria: @js($initialCriteria), addCriterion() { if (this.criteria.length < 15) this.criteria.push({ label: '', score: '' }) }, removeCriterion(index) { if (this.criteria.length > 1) this.criteria.splice(index, 1) } }">
    <template x-for="(criterion, index) in criteria" :key="index">
        <div class="grid gap-3 rounded-xl border border-line bg-bg/50 p-3 sm:grid-cols-[1fr_7rem_auto] sm:items-end">
            <div>
                <label class="text-xs font-medium text-muted" :for="`criteria-label-${index}`">Nama indikator</label>
                <input :id="`criteria-label-${index}`" :name="`criteria[${index}][label]`" x-model="criterion.label" maxlength="120" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
            </div>
            <div>
                <label class="text-xs font-medium text-muted" :for="`criteria-score-${index}`">Nilai (1–5)</label>
                <input :id="`criteria-score-${index}`" :name="`criteria[${index}][score]`" x-model="criterion.score" type="number" min="1" max="5" class="mt-1 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/15" required>
            </div>
            <button type="button" @click="removeCriterion(index)" :disabled="criteria.length === 1" :aria-label="`Hapus indikator ${index + 1}`" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-white text-muted transition hover:border-red-300 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40">
                <span class="material-symbols-outlined text-[18px]">delete</span>
            </button>
        </div>
    </template>
    <button type="button" @click="addCriterion()" :disabled="criteria.length >= 15" class="inline-flex items-center gap-1.5 rounded-xl border border-primary/30 px-3 py-2 text-sm font-semibold text-primary-dark transition hover:bg-primary/5 disabled:cursor-not-allowed disabled:opacity-50">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Tambah indikator
    </button>
</div>