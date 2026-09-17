<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('department_needs')) {
            return;
        }

        Schema::create('department_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('prodi');
            $table->string('department')->nullable();
            $table->string('purpose');
            $table->text('academic_needs')->nullable();
            $table->text('problem')->nullable();
            $table->text('goal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_needs');
    }
};
