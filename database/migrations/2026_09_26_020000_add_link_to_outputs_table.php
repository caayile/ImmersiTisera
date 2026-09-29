<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional external link (e.g. Drive/docs URL) entered by the
     * participant on upload. Shown as the "Hasil" button target.
     */
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->string('link', 2048)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
