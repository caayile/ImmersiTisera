<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
            $table->string('verification_status')->default('pending_profile')->after('role');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->string('phone')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'verification_status', 'rejection_reason', 'phone']);
        });
    }
};
