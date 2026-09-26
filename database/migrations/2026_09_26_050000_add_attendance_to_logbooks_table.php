<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attendance type filled by the participant: Hadir, Izin, Sakit,
     * or Tanpa Keterangan. Null means Hadir (entries created before
     * this column existed).
     */
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->string('attendance', 32)->nullable()->after('activity');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn('attendance');
        });
    }
};
