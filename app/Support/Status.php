<?php

namespace App\Support;

class Status
{
    public const APPLICATION = ['draft', 'submitted', 'waiting_mentor', 'waiting_admin', 'approved', 'revision', 'rejected'];

    public const AGREEMENT = ['draft', 'submitted', 'revision', 'agreed'];

    public const PROGRAM = ['draft', 'submitted', 'revision', 'agreed', 'active', 'completed'];

    public const LOGBOOK = ['draft', 'submitted', 'reviewed', 'revision', 'approved'];

    public const OUTPUT = ['draft', 'submitted', 'revision', 'approved'];

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
        1 => ['phase' => 'discover', 'title' => 'TEMUKAN', 'output' => 'Wawasan Industri', 'description' => 'Orientasi, observasi bisnis, serta pemahaman departemen, tim, dan alur kerja.'],
        2 => ['phase' => 'discover', 'title' => 'TEMUKAN', 'output' => 'Wawasan Industri', 'description' => 'Lanjutkan observasi dan rangkum wawasan industri.'],
        3 => ['phase' => 'understand', 'title' => 'PAHAMI', 'output' => 'Rumusan Masalah', 'description' => 'Eksplorasi masalah, identifikasi peluang, analisis, dan validasi.'],
        4 => ['phase' => 'understand', 'title' => 'PAHAMI', 'output' => 'Rumusan Masalah', 'description' => 'Finalisasi rumusan masalah bersama mentor berdasarkan kondisi nyata.'],
        5 => ['phase' => 'contribute', 'title' => 'BERKONTRIBUSI', 'output' => 'Draf Solusi / Prototipe / Laporan', 'description' => 'Kerjakan tugas, riset, analisis, dan perbaikan bersama mentor.'],
        6 => ['phase' => 'contribute', 'title' => 'BERKONTRIBUSI', 'output' => 'Draf Solusi / Prototipe / Laporan', 'description' => 'Lanjutkan kontribusi dan evaluasi kemajuan mingguan.'],
        7 => ['phase' => 'contribute', 'title' => 'BERKONTRIBUSI', 'output' => 'Draf Solusi / Prototipe / Laporan', 'description' => 'Sempurnakan hasil sebelum minggu terakhir.'],
        8 => ['phase' => 'deliver', 'title' => 'SERAHKAN', 'output' => 'Hasil Utama', 'description' => 'Finalisasi, presentasi, dan refleksi.'],
    ];

    private const LEGACY_TIMELINE_TEXT = [
        'DISCOVER' => 'TEMUKAN',
        'UNDERSTAND' => 'PAHAMI',
        'CONTRIBUTE' => 'BERKONTRIBUSI',
        'DELIVER' => 'SERAHKAN',
        'Industry Insight' => 'Wawasan Industri',
        'Problem Statement' => 'Rumusan Masalah',
        'Draft Solution / Prototype / Report' => 'Draf Solusi / Prototipe / Laporan',
        'Main Deliverable' => 'Hasil Utama',
        'Orientation, business observation, understanding department, team, and workflow.' => 'Orientasi, observasi bisnis, serta pemahaman departemen, tim, dan alur kerja.',
        'Continue observation and capture industry insight.' => 'Lanjutkan observasi dan rangkum wawasan industri.',
        'Problem exploration, opportunity identification, analysis, validation.' => 'Eksplorasi masalah, identifikasi peluang, analisis, dan validasi.',
        'Finalize problem statement with mentor reality check.' => 'Finalisasi rumusan masalah bersama mentor berdasarkan kondisi nyata.',
        'Task, research, analysis, improvement, iteration with mentor.' => 'Kerjakan tugas, riset, analisis, dan perbaikan bersama mentor.',
        'Continue contribution and weekly checkpoint.' => 'Lanjutkan kontribusi dan evaluasi kemajuan mingguan.',
        'Iterate deliverable before final week.' => 'Sempurnakan hasil sebelum minggu terakhir.',
        'Finalization, presentation, and reflection.' => 'Finalisasi, presentasi, dan refleksi.',
    ];

    public static function timelineText(?string $value): ?string
    {
        return $value === null ? null : (self::LEGACY_TIMELINE_TEXT[$value] ?? $value);
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
