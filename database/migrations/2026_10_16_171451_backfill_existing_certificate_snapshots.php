<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $signatory = DB::table('certificate_signatories')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        DB::table('certificates as certificates')
            ->join('programs', 'programs.id', '=', 'certificates.program_id')
            ->leftJoin('applications', 'applications.id', '=', 'programs.application_id')
            ->leftJoin('participants', 'participants.id', '=', 'programs.participant_id')
            ->leftJoin('users as participant_users', 'participant_users.id', '=', 'participants.user_id')
            ->leftJoin('mentors', 'mentors.id', '=', 'programs.mentor_id')
            ->leftJoin('users as mentor_users', 'mentor_users.id', '=', 'mentors.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'programs.department_id')
            ->leftJoin('business_units', 'business_units.id', '=', 'programs.business_unit_id')
            ->whereNull('certificates.snapshot')
            ->select([
                'certificates.id',
                'participant_users.name as participant_name',
                'departments.name as department_name',
                'business_units.name as business_unit_name',
                'mentor_users.name as mentor_name',
                'applications.batch as application_batch',
                'business_units.batch as unit_batch',
            ])
            ->orderBy('certificates.id')
            ->get()
            ->each(function (object $certificate) use ($signatory): void {
                $batch = $certificate->application_batch ?? $certificate->unit_batch ?? '1';

                DB::table('certificates')->where('id', $certificate->id)->update([
                    'snapshot' => json_encode([
                        'participant_name' => $certificate->participant_name,
                        'department_name' => $certificate->department_name,
                        'business_unit_name' => $certificate->business_unit_name,
                        'mentor_name' => $certificate->mentor_name,
                        'batch' => (string) $batch,
                    ], JSON_THROW_ON_ERROR),
                    'signatory_snapshot' => json_encode($signatory ? [
                        'name' => $signatory->name,
                        'title' => $signatory->title,
                        'organization' => $signatory->organization,
                        'signature_path' => $signatory->signature_path,
                    ] : [], JSON_THROW_ON_ERROR),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('certificates')->update([
            'snapshot' => null,
            'signatory_snapshot' => null,
        ]);
    }
};
