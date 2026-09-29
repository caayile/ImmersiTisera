<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each of Hasil (project) and Laporan (report document) accepts
     * either a link or an uploaded file, never both:
     * - Hasil: link (exists) xor hasil_file_path (new)
     * - Laporan: file_path (exists) xor laporan_link (new)
     */
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->string('hasil_file_path')->nullable()->after('link');
            $table->string('laporan_link', 2048)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropColumn(['hasil_file_path', 'laporan_link']);
        });
    }
};
