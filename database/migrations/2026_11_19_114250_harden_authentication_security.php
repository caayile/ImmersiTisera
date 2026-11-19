<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table): void {
            $table->string('otp', 255)->nullable()->change();
            $table->string('otp_hash', 255)->nullable()->after('otp');
            $table->string('token', 64)->nullable()->after('otp_hash');
            $table->unsignedInteger('attempts')->default(0)->after('token');
        });

        Schema::table('registration_otps', function (Blueprint $table): void {
            $table->string('otp', 255)->nullable()->change();
            $table->string('otp_hash', 255)->nullable()->after('otp');
            $table->unsignedInteger('attempts')->default(0)->after('otp_hash');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('two_factor_secret', 255)->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });

        Schema::table('registration_otps', function (Blueprint $table): void {
            $table->dropColumn(['otp_hash', 'attempts']);
            $table->string('otp', 6)->nullable(false)->change();
        });

        Schema::table('password_reset_otps', function (Blueprint $table): void {
            $table->dropColumn(['otp_hash', 'token', 'attempts']);
            $table->string('otp', 6)->nullable(false)->change();
        });
    }
};
