<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('batch')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasNonNumericBatch = DB::table('certificates')
            ->whereNotNull('batch')
            ->get(['batch'])
            ->contains(fn (object $certificate): bool => ! ctype_digit((string) $certificate->batch));

        if ($hasNonNumericBatch) {
            throw new RuntimeException('Batch teks tidak dapat dikembalikan ke tipe angka tanpa kehilangan data.');
        }

        Schema::table('certificates', function (Blueprint $table) {
            $table->unsignedInteger('batch')->nullable()->change();
        });
    }
};
