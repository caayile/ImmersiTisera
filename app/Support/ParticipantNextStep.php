<?php

namespace App\Support;

use App\Models\User;

class ParticipantNextStep
{
    public function __construct(
        public string $eyebrow,
        public string $title,
        public string $message,
        public string $cta,
        public string $url,
    ) {}

    public static function for(User $user): ?self
    {
        if (! $user->isParticipant()) {
            return null;
        }

        $participant = $user->participant;

        if (! $participant || ! filled($participant->study_program)) {
            return new self(
                eyebrow: 'Langkah berikutnya',
                title: 'Lengkapi profil dosen',
                message: 'Isi fakultas dan program studi dulu agar bisa mendaftar ke unit bisnis.',
                cta: 'Lengkapi profil',
                url: route('participant.profile'),
            );
        }

        $application = $participant->applications()->latest('id')->first();
        $program = $participant->programs()->latest('id')->first();

        if ($application?->status === 'revision') {
            return new self(
                eyebrow: 'Langkah berikutnya',
                title: 'Perbaiki pendaftaran',
                message: 'Ada catatan revisi. Lengkapi form lalu kirim ulang.',
                cta: 'Perbaiki form',
                url: route('participant.applications.edit', $application),
            );
        }

        if ($application && in_array($application->status, ['submitted', 'waiting_mentor', 'waiting_admin'], true)) {
            return new self(
                eyebrow: 'Langkah berikutnya',
                title: 'Pendaftaran sedang ditinjau',
                message: $application->currentStageLabel().'. Cek status kapan saja di riwayat pendaftaran.',
                cta: 'Lihat status',
                url: route('participant.applications.show', $application),
            );
        }

        if ($program && $program->status === 'active') {
            return new self(
                eyebrow: 'Langkah berikutnya',
                title: 'Isi logbook minggu ini',
                message: 'Program imersi sudah aktif. Catat aktivitas harian agar progres tetap terukur.',
                cta: 'Buka logbook',
                url: route('participant.logbooks'),
            );
        }

        if ($program && in_array($program->status, ['draft', 'submitted', 'revision', 'agreed'], true)) {
            return new self(
                eyebrow: 'Langkah berikutnya',
                title: 'Lanjutkan perjanjian imersi',
                message: 'Pendaftaran disetujui. Lengkapi perjanjian sebelum program dimulai.',
                cta: 'Buka perjanjian',
                url: route('participant.agreement'),
            );
        }

        if ($program && $program->status === 'completed') {
            return null;
        }

        return new self(
            eyebrow: 'Langkah berikutnya',
            title: 'Daftar ke unit bisnis',
            message: 'Pilih unit yang selaras dengan program studi Anda, lalu kirim form pendaftaran.',
            cta: 'Jelajahi unit bisnis',
            url: route('departments.index'),
        );
    }
}
