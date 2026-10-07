<?php

namespace App\Support;

class Status
{
    public const APPLICATION = ['draft', 'submitted', 'waiting_mentor', 'waiting_admin', 'approved', 'revision', 'rejected'];

    public const AGREEMENT = ['draft', 'submitted', 'revision', 'agreed'];

    public const PROGRAM = ['draft', 'submitted', 'revision', 'agreed', 'active', 'completed'];

    public const LOGBOOK = ['draft', 'submitted', 'reviewed', 'revision', 'approved'];

    public const OUTPUT = ['draft', 'submitted', 'revision', 'approved'];

    /**
     * Empat checkpoint skema 1-3-3-1:
     * minggu 1 Orientasi · minggu 2–4 Observasi · minggu 5–7 Kolaborasi · minggu 8 Laporan/Hasil.
     */
    public const CHECKPOINT_WEEKS = [1, 2, 5, 8];

    public const OUTPUT_TYPES = [
        'Project', 'Improvement', 'SOP', 'Prototype', 'Design', 'Campaign',
        'Insight', 'Recommendation', 'Process Mapping', 'Research Report',
        'Market Insight', 'Product Concept', 'Hasil', 'Bukti Dokumentasi',
    ];

    public const COLLABORATION_TYPES = [
        'Guest Lecture', 'Student Project', 'Research', 'Publication',
        'Curriculum Development', 'Product Development', 'Industry-Based Learning',
    ];

    public const COLLABORATION_LEVELS = [
        0 => 'Ditutup',
        1 => 'Tindak Lanjut',
        2 => 'Kolaborasi',
        3 => 'Pengembangan',
        4 => 'Perluasan',
    ];

    public const COLLABORATION_LEVEL_DESCRIPTIONS = [
        0 => 'Selesai, tanpa tindak lanjut.',
        1 => 'Perlu diskusi lanjutan.',
        2 => 'Proyek bersama.',
        3 => 'Prototipe, riset, atau ide dikembangkan.',
        4 => 'Menjadi program strategis.',
    ];

    public const TIMELINE = [
        1 => [
            'phase' => 'orientasi',
            'title' => 'ORIENTASI',
            'output' => 'Catatan Orientasi',
            'description' => 'Khusus minggu 1: orientasi budaya unit, tim, alur kerja, dan penyelarasan harapan bersama mentor.',
            'window' => 'Minggu 1',
        ],
        2 => [
            'phase' => 'observasi',
            'title' => 'OBSERVASI',
            'output' => 'Ringkasan Observasi',
            'description' => 'Minggu 2–4: observasi proses bisnis, petakan hambatan/peluang, dan rangkum temuan bersama mentor.',
            'window' => 'Minggu 2–4',
        ],
        5 => [
            'phase' => 'kolaborasi',
            'title' => 'KOLABORASI',
            'output' => 'Draf Kolaborasi',
            'description' => 'Minggu 5–7: kolaborasi terapan (tugas bersama, riset, atau perbaikan proses) dan evaluasi kemajuan.',
            'window' => 'Minggu 5–7',
        ],
        8 => [
            'phase' => 'hasil',
            'title' => 'LAPORAN / HASIL',
            'output' => 'Laporan / Hasil Utama',
            'description' => 'Khusus minggu 8: finalisasi laporan atau hasil, presentasi, dan refleksi penutupan program.',
            'window' => 'Minggu 8',
        ],
    ];

    private const LEGACY_TIMELINE_TEXT = [
        'DISCOVER' => 'ORIENTASI',
        'UNDERSTAND' => 'OBSERVASI',
        'CONTRIBUTE' => 'KOLABORASI',
        'DELIVER' => 'LAPORAN / HASIL',
        'TEMUKAN' => 'ORIENTASI',
        'PAHAMI' => 'OBSERVASI',
        'BERKONTRIBUSI' => 'KOLABORASI',
        'SERAHKAN' => 'LAPORAN / HASIL',
        'Industry Insight' => 'Catatan Orientasi',
        'Problem Statement' => 'Ringkasan Observasi',
        'Draft Solution / Prototype / Report' => 'Draf Kolaborasi',
        'Main Deliverable' => 'Laporan / Hasil Utama',
        'Wawasan Industri' => 'Catatan Orientasi',
        'Rumusan Masalah' => 'Ringkasan Observasi',
        'Draf Solusi / Prototipe / Laporan' => 'Draf Kolaborasi',
        'Hasil Utama' => 'Laporan / Hasil Utama',
        'Orientation, business observation, understanding department, team, and workflow.' => 'Orientasi budaya unit, tim, alur kerja, dan penyelarasan harapan bersama mentor.',
        'Continue observation and capture industry insight.' => 'Mulai observasi proses bisnis, keputusan, dan pola kerja di unit.',
        'Problem exploration, opportunity identification, analysis, validation.' => 'Lanjutkan observasi dan petakan hambatan serta peluang unit.',
        'Finalize problem statement with mentor reality check.' => 'Rangkum temuan observasi dan validasikan bersama mentor.',
        'Task, research, analysis, improvement, iteration with mentor.' => 'Mulai kolaborasi terapan: tugas bersama, riset, atau perbaikan proses.',
        'Continue contribution and weekly checkpoint.' => 'Lanjutkan kolaborasi dan evaluasi kemajuan mingguan bersama mentor.',
        'Iterate deliverable before final week.' => 'Sempurnakan hasil kolaborasi menjelang minggu laporan.',
        'Finalization, presentation, and reflection.' => 'Finalisasi laporan atau hasil, presentasi, dan refleksi penutupan program.',
    ];

    public static function timelineText(?string $value): ?string
    {
        return $value === null ? null : (self::LEGACY_TIMELINE_TEXT[$value] ?? $value);
    }

    public static function checkpointWindowLabel(int $week): string
    {
        return self::TIMELINE[$week]['window'] ?? 'Minggu '.$week;
    }

    /**
     * @return array{phase: string, title: string, output: string, description: string, window: string}|null
     */
    public static function checkpointMeta(int $week): ?array
    {
        return self::TIMELINE[$week] ?? null;
    }

    public static function isLegacyCheckpointLabel(?string $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return array_key_exists($value, self::LEGACY_TIMELINE_TEXT)
            || in_array($value, [
                'TEMUKAN', 'PAHAMI', 'BERKONTRIBUSI', 'SERAHKAN',
                'DISCOVER', 'UNDERSTAND', 'CONTRIBUTE', 'DELIVER',
                'ORIENTASI', 'OBSERVASI', 'KOLABORASI', 'LAPORAN / HASIL',
                'Wawasan Industri', 'Rumusan Masalah', 'Draf Solusi / Prototipe / Laporan', 'Hasil Utama',
                'Catatan Orientasi', 'Ringkasan Observasi', 'Draf Kolaborasi', 'Laporan / Hasil Utama',
            ], true);
    }

    public static function label(string $status): string
    {
        return match ($status) {
            'draft' => 'Draf',
            'submitted' => 'Menunggu admin',
            'waiting_mentor' => 'Menunggu mentor',
            'waiting_admin' => 'Menunggu pengesahan',
            'approved' => 'Disetujui',
            'revision' => 'Revisi',
            'rejected' => 'Ditolak',
            'agreed' => 'Disepakati',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'reviewed' => 'Ditinjau',
            'open' => 'Dibuka',
            'closed' => 'Ditutup',
            'disabled' => 'Nonaktif',
            'pending' => 'Menunggu',
            'done' => 'Selesai',
            default => str_replace('_', ' ', $status),
        };
    }

    /**
     * Status label for outputs/final reports, reviewed by mentors
     * (unlike applications, which wait for admin).
     */
    public static function outputLabel(string $status): string
    {
        return $status === 'submitted' ? 'Menunggu mentor' : self::label($status);
    }

    public static function logbookLabel(string $status): string
    {
        return $status === 'submitted' ? 'Menunggu mentor' : self::label($status);
    }

    public static function outputTypeLabel(string $type): string
    {
        return match ($type) {
            'Project' => 'Proyek',
            'Improvement' => 'Peningkatan',
            'SOP' => 'SOP',
            'Prototype' => 'Prototipe',
            'Design' => 'Desain',
            'Campaign' => 'Kampanye',
            'Insight' => 'Wawasan',
            'Recommendation' => 'Rekomendasi',
            'Process Mapping' => 'Pemetaan Proses',
            'Research Report' => 'Laporan Riset',
            'Market Insight' => 'Wawasan Pasar',
            'Product Concept' => 'Konsep Produk',
            'Bukti Dokumentasi' => 'Bukti Dokumentasi',
            default => $type,
        };
    }

    public static function collaborationTypeLabel(string $type): string
    {
        return match ($type) {
            'Guest Lecture' => 'Kuliah Tamu',
            'Student Project' => 'Proyek Mahasiswa',
            'Research' => 'Penelitian',
            'Publication' => 'Publikasi',
            'Curriculum Development' => 'Pengembangan Kurikulum',
            'Product Development' => 'Pengembangan Produk',
            'Industry-Based Learning' => 'Pembelajaran Berbasis Industri',
            default => $type,
        };
    }

    public static function collaborationLevelLabel(int $level): string
    {
        return self::COLLABORATION_LEVELS[$level] ?? 'Tidak diketahui';
    }

    public static function badge(string $status): string
    {
        return match ($status) {
            'approved', 'agreed', 'active', 'completed', 'open' => 'bg-emerald-50 text-emerald-700',
            'submitted', 'reviewed', 'waiting_mentor', 'waiting_admin' => 'bg-sky-50 text-sky-700',
            'revision', 'draft' => 'bg-amber-50 text-amber-700',
            'rejected' => 'bg-red-50 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }
}
