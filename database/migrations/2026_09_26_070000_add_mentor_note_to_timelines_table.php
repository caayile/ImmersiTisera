<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->text('mentor_note')->nullable()->after('expected_output');
        });
    }

    public function down(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropColumn('mentor_note');
        });
    }
};
