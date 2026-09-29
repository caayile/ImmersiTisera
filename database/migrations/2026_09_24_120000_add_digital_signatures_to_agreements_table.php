<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->text('participant_signature')->nullable()->after('participant_approved_at');
            $table->text('mentor_signature')->nullable()->after('mentor_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['participant_signature', 'mentor_signature']);
        });
    }
};
