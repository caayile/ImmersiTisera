<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['program_id', 'number', 'global_sequence', 'batch', 'issued_month', 'issued_year', 'status', 'mentor_signature', 'issued_at'])]
class Certificate extends Model
{
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued' && $this->issued_at !== null;
    }

    /**
     * Generate nomor sertifikat dengan format: 001/MD.TSU/B1/X/2026
     * - 001: nomor urut global (3 digit minimum)
     * - MD.TSU: kode tetap
     * - B1: nomor batch program
     * - X: bulan penerbitan dalam angka Romawi (I-XII)
     * - 2026: tahun penerbitan
     */
    public static function generateCertificateNumber(int $globalSequence, int $batch, int $month, int $year): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $sequenceFormatted = str_pad((string) $globalSequence, 3, '0', STR_PAD_LEFT);
        $romanMonth = $romanMonths[$month] ?? 'I';

        return sprintf('%s/MD.TSU/B%d/%s/%d', $sequenceFormatted, $batch, $romanMonth, $year);
    }

    /**
     * Generate nomor urut global berikutnya dengan menggunakan database lock untuk mencegah race condition.
     * Menggunakan raw query dengan FOR UPDATE untuk memastikan atomicity.
     */
    public static function getNextGlobalSequence(): int
    {
        return \DB::transaction(function () {
            // Lock dan ambil sequence tertinggi yang ada
            $maxSequence = self::query()
                ->lockForUpdate()
                ->max('global_sequence');

            return ($maxSequence ?? 0) + 1;
        });
    }
}
