<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('letter_number')->nullable()->unique()->after('status');
            $table->timestamp('letter_issued_at')->nullable()->after('letter_number');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['letter_number', 'letter_issued_at']);
        });
    }
};
