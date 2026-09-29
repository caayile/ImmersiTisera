<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hero_settings')
            ->where('page', 'departments')
            ->where('title', 'Departemen Mitra Imersi')
            ->update(['title' => 'Unit Bisnis Mitra Magang Dosen']);
    }

    public function down(): void
    {
        DB::table('hero_settings')
            ->where('page', 'departments')
            ->where('title', 'Unit Bisnis Mitra Magang Dosen')
            ->update(['title' => 'Departemen Mitra Imersi']);
    }
};
