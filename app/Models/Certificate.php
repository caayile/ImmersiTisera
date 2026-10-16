<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

#[Fillable(['program_id', 'number', 'global_sequence', 'batch', 'issued_month', 'issued_year', 'status', 'mentor_signature', 'mentor_signature_path', 'snapshot', 'signatory_snapshot', 'issued_at'])]
class Certificate extends Model
{
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'snapshot' => 'array',
            'signatory_snapshot' => 'array',
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
    public static function generateCertificateNumber(int $globalSequence, string|int $batch, int $month, int $year): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        if (! isset($romanMonths[$month])) {
            throw new InvalidArgumentException('Bulan penerbitan harus berada di antara 1 dan 12.');
        }

        $sequenceFormatted = str_pad((string) $globalSequence, 3, '0', STR_PAD_LEFT);
        $romanMonth = $romanMonths[$month];

        return sprintf('NO. %s/MD.TSU/B%s/%s/%d', $sequenceFormatted, $batch, $romanMonth, $year);
    }

    /**
     * Generate nomor urut global berikutnya dengan menggunakan database lock untuk mencegah race condition.
     * Menggunakan raw query dengan FOR UPDATE untuk memastikan atomicity.
     */
    public static function getNextGlobalSequence(): int
    {
        return DB::transaction(function (): int {
            $counter = DB::table('certificate_sequences')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                throw new \RuntimeException('Counter nomor sertifikat belum tersedia.');
            }

            $nextSequence = (int) $counter->last_sequence + 1;

            DB::table('certificate_sequences')
                ->where('id', 1)
                ->update(['last_sequence' => $nextSequence, 'updated_at' => now()]);

            return $nextSequence;
        });
    }
}
