<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track which registration batch an application belongs to, so the
     * per-lowongan quota (2 pendaftar) resets every time admin opens a
     * new batch. Existing applications inherit their unit's current batch
     * so running batches keep their quota counts.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('batch', 120)->nullable()->after('business_unit_id');
        });

        foreach (DB::table('business_units')->pluck('batch', 'id') as $unitId => $batch) {
            if ($batch === null) {
                continue;
            }

            DB::table('applications')
                ->where('business_unit_id', $unitId)
                ->whereNull('batch')
                ->update(['batch' => $batch]);
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('batch');
        });
    }
};
