<?php

use App\Models\EvaluationScale;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Ganti skala kuesioner bawaan menjadi tingkat persetujuan.
     */
    public function up(): void
    {
        $this->rename([
            1 => 'Sangat Tidak Setuju',
            2 => 'Tidak Setuju',
            3 => 'Cukup',
            4 => 'Setuju',
            5 => 'Sangat Setuju',
        ]);
    }

    /**
     * Kembalikan label skala bawaan sebelumnya.
     */
    public function down(): void
    {
        $this->rename([
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
        ]);
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function rename(array $labels): void
    {
        foreach ($labels as $value => $label) {
            EvaluationScale::query()->where('value', $value)->update(['label' => $label]);
        }
    }
};
