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
        'Market Insight', 'Product Concept',
    ];

    public const COLLABORATION_TYPES = [
        'Guest Lecture', 'Student Project', 'Research', 'Publication',
        'Curriculum Development', 'Product Development', 'Industry-Based Learning',
    ];

    public const COLLABORATION_LEVELS = [
        0 => 'Close',
        1 => 'Follow-up',
        2 => 'Collaborate',
        3 => 'Develop',
        4 => 'Scale',
    ];

    public const TIMELINE = [
        1 => ['phase' => 'discover', 'title' => 'DISCOVER', 'output' => 'Industry Insight', 'description' => 'Orientation, business observation, understanding department, team, and workflow.'],
        2 => ['phase' => 'discover', 'title' => 'DISCOVER', 'output' => 'Industry Insight', 'description' => 'Continue observation and capture industry insight.'],
        3 => ['phase' => 'understand', 'title' => 'UNDERSTAND', 'output' => 'Problem Statement', 'description' => 'Problem exploration, opportunity identification, analysis, validation.'],
        4 => ['phase' => 'understand', 'title' => 'UNDERSTAND', 'output' => 'Problem Statement', 'description' => 'Finalize problem statement with mentor reality check.'],
        5 => ['phase' => 'contribute', 'title' => 'CONTRIBUTE', 'output' => 'Draft Solution / Prototype / Report', 'description' => 'Task, research, analysis, improvement, iteration with mentor.'],
        6 => ['phase' => 'contribute', 'title' => 'CONTRIBUTE', 'output' => 'Draft Solution / Prototype / Report', 'description' => 'Continue contribution and weekly checkpoint.'],
        7 => ['phase' => 'contribute', 'title' => 'CONTRIBUTE', 'output' => 'Draft Solution / Prototype / Report', 'description' => 'Iterate deliverable before final week.'],
        8 => ['phase' => 'deliver', 'title' => 'DELIVER', 'output' => 'Main Deliverable', 'description' => 'Finalization, presentation, and reflection.'],
    ];

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
            default => str_replace('_', ' ', $status),
        };
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
