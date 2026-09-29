<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->longText('participant_signature')->nullable()->after('indicator_feedback');
            $table->longText('mentor_signature')->nullable()->after('participant_signature');
            $table->timestamp('participant_signed_at')->nullable()->after('mentor_signature');
            $table->timestamp('mentor_signed_at')->nullable()->after('participant_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'participant_signature',
                'mentor_signature',
                'participant_signed_at',
                'mentor_signed_at',
            ]);
        });
    }
};
