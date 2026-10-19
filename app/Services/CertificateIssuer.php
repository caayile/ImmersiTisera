<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateSignatory;
use App\Models\Mentor;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CertificateIssuer
{
    public function issueIfReady(Program $program): ?Certificate
    {
        $program->unsetRelation('evaluations');
        $program->unsetRelation('certificate');
        $program->loadMissing(['mentor', 'certificate', 'evaluations', 'participant']);

        if ($program->certificate?->isIssued() && $program->certificate->global_sequence !== null) {
            return $program->certificate;
        }

        $mentor = $program->mentor;
        $participantUserId = (int) $program->participant?->user_id;
        $hasReport = $program->evaluations->contains(
            fn ($evaluation): bool => (int) $evaluation->evaluator_id !== $participantUserId && $evaluation->hasReport()
        );

        if (! $mentor || ! filled($mentor->certificate_signature) || ! $hasReport) {
            return null;
        }

        try {
            return $this->issue($program, $mentor);
        } catch (RuntimeException $exception) {
            report($exception);

            return null;
        }
    }

    public function issueReadyForMentor(Mentor $mentor): void
    {
        $mentor->programs()->get()->each(function (Program $program): void {
            $this->issueIfReady($program);
        });
    }

    public function issue(Program $program, Mentor $mentor): Certificate
    {
        return DB::transaction(function () use ($program, $mentor): Certificate {
            $lockedProgram = Program::query()
                ->whereKey($program->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $certificate = Certificate::query()
                ->where('program_id', $lockedProgram->id)
                ->lockForUpdate()
                ->first();

            if ($certificate?->number && $certificate->global_sequence !== null) {
                return $certificate;
            }

            $lockedProgram->loadMissing([
                'application',
                'participant.user',
                'department',
                'businessUnit',
                'mentor.user',
            ]);

            $signatory = CertificateSignatory::query()
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if (! $signatory) {
                throw new RuntimeException('Pejabat penandatangan sertifikat belum dikonfigurasi.');
            }

            $issuedAt = now();
            $batchLabel = (string) ($lockedProgram->application?->batch
                ?? $lockedProgram->businessUnit?->batch
                ?? '1');
            $batchCode = ctype_digit($batchLabel) ? $batchLabel : '1';
            $sequence = Certificate::getNextGlobalSequence();
            $number = Certificate::generateCertificateNumber(
                $sequence,
                $batchCode,
                (int) $issuedAt->format('n'),
                (int) $issuedAt->format('Y'),
            );
            $mentorSignature = (string) $mentor->certificate_signature;
            $signaturePath = $this->snapshotSignature($mentorSignature, $sequence, 'mentor');
            $signatorySignaturePath = $this->snapshotSignature($signatory->signature_path, $sequence, 'signatory');

            $attributes = [
                'number' => $number,
                'global_sequence' => $sequence,
                'batch' => (int) $batchCode,
                'issued_month' => (int) $issuedAt->format('n'),
                'issued_year' => (int) $issuedAt->format('Y'),
                'status' => 'issued',
                'mentor_signature' => $signaturePath === null ? $mentorSignature : null,
                'mentor_signature_path' => $signaturePath,
                'issued_at' => $issuedAt,
                'snapshot' => [
                    'participant_name' => $lockedProgram->participant?->user?->name,
                    'department_name' => $lockedProgram->department?->name,
                    'business_unit_name' => $lockedProgram->businessUnit?->name,
                    'mentor_name' => $lockedProgram->mentor?->user?->name,
                    'batch' => $batchLabel,
                ],
                'signatory_snapshot' => [
                    'name' => $signatory->name,
                    'title' => $signatory->title,
                    'organization' => $signatory->organization,
                    'signature_path' => $signatorySignaturePath,
                ],
            ];

            if ($certificate) {
                $certificate->update($attributes);

                return $certificate->refresh();
            }

            return Certificate::query()->create([
                ...$attributes,
                'program_id' => $lockedProgram->id,
            ]);
        });
    }

    private function snapshotSignature(?string $sourcePath, int $sequence, string $role): ?string
    {
        if (! $sourcePath || str_starts_with($sourcePath, 'data:image/')) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $snapshotPath = sprintf('certificates/issued/%03d-%s.%s', $sequence, $role, $extension);

        if (! $disk->copy($sourcePath, $snapshotPath)) {
            throw new RuntimeException('Tanda tangan sertifikat gagal disalin ke snapshot.');
        }

        return $snapshotPath;
    }
}
