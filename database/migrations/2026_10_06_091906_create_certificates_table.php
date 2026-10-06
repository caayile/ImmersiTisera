<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->longText('certificate_signature')->nullable()->after('expertise');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('number')->nullable()->unique();
            $table->string('status')->default('issued');
            $table->longText('mentor_signature')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique('program_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');

        Schema::table('mentors', function (Blueprint $table) {
            $table->dropColumn('certificate_signature');
        });
    }
};
