<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificate_sequences', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->json('snapshot')->nullable();
            $table->json('signatory_snapshot')->nullable();
            $table->string('mentor_signature_path')->nullable();
        });

        Schema::create('certificate_signatories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->string('organization');
            $table->string('signature_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $usedSequences = DB::table('certificates')
            ->whereNotNull('global_sequence')
            ->pluck('global_sequence')
            ->map(fn ($sequence): int => (int) $sequence)
            ->all();
        $nextSequence = max($usedSequences ?: [0]);

        DB::table('certificates')
            ->whereNull('global_sequence')
            ->whereNotNull('number')
            ->orderBy('id')
            ->get(['id', 'number'])
            ->each(function (object $certificate) use (&$usedSequences, &$nextSequence): void {
                preg_match('/^(\d+)\//', (string) $certificate->number, $matches);
                $sequence = isset($matches[1]) ? (int) $matches[1] : 0;

                if ($sequence === 0 || in_array($sequence, $usedSequences, true)) {
                    $sequence = ++$nextSequence;
                    $number = preg_replace('/^\d+\//', str_pad((string) $sequence, 3, '0', STR_PAD_LEFT).'/', (string) $certificate->number);
                } else {
                    $nextSequence = max($nextSequence, $sequence);
                    $number = $certificate->number;
                }

                DB::table('certificates')->where('id', $certificate->id)->update([
                    'number' => $number,
                    'global_sequence' => $sequence,
                ]);

                $usedSequences[] = $sequence;
            });

        DB::table('certificate_sequences')->insert([
            'id' => 1,
            'last_sequence' => $nextSequence,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('certificate_signatories')->insert([
            'name' => 'Erfan Zaenuddin',
            'title' => 'Center of Excellence Manager',
            'organization' => 'PT Tiga Serangkai Inti Corpora',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_signatories');
        Schema::dropIfExists('certificate_sequences');

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['snapshot', 'signatory_snapshot', 'mentor_signature_path']);
        });
    }
};
