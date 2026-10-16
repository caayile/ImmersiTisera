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
            $batch = (string) ($lockedProgram->application?->batch
                ?? $lockedProgram->businessUnit?->batch
                ?? '1');
            $sequence = Certificate::getNextGlobalSequence();
            $number = Certificate::generateCertificateNumber(
                $sequence,
                $batch,
                (int) $issuedAt->format('n'),
                (int) $issuedAt->format('Y'),
            );
            $mentorSignature = (string) $mentor->certificate_signature;
            $signaturePath = $this->snapshotSignature($mentorSignature, $sequence, 'mentor');
            $signatorySignaturePath = $this->snapshotSignature($signatory->signature_path, $sequence, 'signatory');

            $attributes = [
                'number' => $number,
                'global_sequence' => $sequence,
                'batch' => ctype_digit($batch) ? (int) $batch : null,
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
                    'batch' => $batch,
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
