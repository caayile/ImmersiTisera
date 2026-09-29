<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One entry per program per date: keep only the newest record of
     * each (program_id, date) pair, delete older duplicates created
     * before resubmits became updates.
     */
    public function up(): void
    {
        $keep = DB::table('logbooks')
            ->selectRaw('MAX(id) as id')
            ->groupBy('program_id', DB::raw('DATE(date)'))
            ->pluck('id');

        if ($keep->isNotEmpty()) {
            DB::table('logbooks')->whereNotIn('id', $keep)->delete();
        }
    }

    public function down(): void
    {
        // Deleted duplicates cannot be restored.
    }
};
