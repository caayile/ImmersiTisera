<?php

namespace App\Services;

use App\Models\Agreement;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgreementLetterService
{
    /**
     * Format: 001/MD/TSU/TS/10-2026
     * Nomor urut 3 digit, reset setiap bulan (per MM-YYYY).
     */
    public static function previewNextNumber(?CarbonInterface $date = null): string
    {
        $date ??= now();
        $suffix = $date->format('m-Y');
        $count = Agreement::where('letter_number', 'like', '%/MD/TSU/TS/'.$suffix)->count();

        return sprintf('%03d/MD/TSU/TS/%s', $count + 1, $suffix);
    }

    /**
     * Terbitkan nomor surat secara aman (lock agar tidak duplikat).
     * Hanya dipanggil saat agreement disetujui penuh (status agreed).
     */
    public function issue(Agreement $agreement, ?CarbonInterface $date = null): string
    {
        if (filled($agreement->letter_number)) {
            return $agreement->letter_number;
        }

        return DB::transaction(function () use ($agreement, $date) {
            $fresh = Agreement::whereKey($agreement->id)->lockForUpdate()->firstOrFail();

            if (filled($fresh->letter_number)) {
                return $fresh->letter_number;
            }

            $date ??= now();
            $suffix = $date->format('m-Y');

            $last = Agreement::where('letter_number', 'like', '%/MD/TSU/TS/'.$suffix)
                ->lockForUpdate()
                ->orderByDesc('letter_number')
                ->first();

            $next = 1;
            if ($last?->letter_number && preg_match('/^(\d+)\/MD\/TSU\/TS\//', $last->letter_number, $m)) {
                $next = ((int) $m[1]) + 1;
            } else {
                $next = Agreement::where('letter_number', 'like', '%/MD/TSU/TS/'.$suffix)->count() + 1;
            }

            $number = sprintf('%03d/MD/TSU/TS/%s', $next, $suffix);

            $fresh->update([
                'letter_number' => $number,
                'letter_issued_at' => Carbon::now(),
            ]);

            $agreement->setAttribute('letter_number', $number);
            $agreement->setAttribute('letter_issued_at', $fresh->letter_issued_at);

            return $number;
        });
    }
}
