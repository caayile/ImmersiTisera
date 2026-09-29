<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'participant_id', 'department_id', 'business_unit_id', 'batch', 'mentor_id', 'motivation',
    'learning_objectives', 'planned_activities', 'expected_output', 'campus_benefit', 'cv_path',
    'shared_goal', 'activity_types', 'problem_statement', 'main_output',
    'participant_benefit', 'business_benefit', 'success_indicators', 'indicator_feedback',
    'participant_signature', 'mentor_signature', 'participant_signed_at', 'mentor_signed_at',
    'preferred_period', 'period_start', 'period_end', 'match_score', 'relevance_warning',
    'matching_notes', 'letter_number', 'mentor_note', 'revision_note', 'status',
    'admin_reviewed_at', 'mentor_reviewed_at', 'admin_finalized_at',
])]
class Application extends Model
{
    public const ACTIVITY_TYPES = ['observasi', 'riset'];

    public const MIN_SUCCESS_INDICATORS = 2;

    public const MAX_SUCCESS_INDICATORS = 10;

    protected function casts(): array
    {
        return [
            'relevance_warning' => 'boolean',
            'activity_types' => 'array',
            'success_indicators' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
            'participant_signed_at' => 'datetime',
            'mentor_signed_at' => 'datetime',
            'admin_reviewed_at' => 'datetime',
            'mentor_reviewed_at' => 'datetime',
            'admin_finalized_at' => 'datetime',
        ];
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function program()
    {
        return $this->hasOne(Program::class);
    }

    /**
     * Applications occupying quota slots: not rejected and in the unit's
     * current registration batch. Units without a batch set keep the
     * legacy behaviour of counting batch-less applications.
     */
    public function scopeForQuota(Builder $query, BusinessUnit $unit): Builder
    {
        $query->where('status', '!=', 'rejected');

        if ($unit->batch === null) {
            $query->whereNull('batch');
        } else {
            $query->where('batch', $unit->batch);
        }

        return $query;
    }

    public static function periodEndFromStart(CarbonInterface $start): Carbon
    {
        return $start->copy()->addMonthsNoOverflow(2);
    }

    public static function isTwoMonthPeriod(CarbonInterface $start, CarbonInterface $end): bool
    {
        return $end->toDateString() === self::periodEndFromStart($start)->toDateString();
    }

    public function periodLabel(): string
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('d M Y').' – '.$this->period_end->format('d M Y');
        }

        return $this->preferred_period ?: '2 bulan';
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function registrationQuestions(): array
    {
        return [
            [
                'key' => 'motivation',
                'label' => 'Mengapa Anda tertarik mengikuti program imersi di penempatan ini?',
            ],
            [
                'key' => 'learning_objectives',
                'label' => 'Kompetensi atau wawasan industri apa yang ingin dikembangkan selama program?',
            ],
            [
                'key' => 'planned_activities',
                'label' => 'Apa rencana kegiatan Anda selama periode imersi?',
            ],
            [
                'key' => 'expected_output',
                'label' => 'Luaran apa yang ingin dihasilkan di akhir program?',
            ],
            [
                'key' => 'campus_benefit',
                'label' => 'Bagaimana hasil program akan bermanfaat bagi mahasiswa, prodi, atau kampus?',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function registrationQuestionKeys(): array
    {
        return array_column(self::registrationQuestions(), 'key');
    }

    /**
     * @return list<array{key: string, label: string, answer: string}>
     */
    public function registrationAnswers(): array
    {
        return array_map(function (array $question): array {
            $question['answer'] = (string) ($this->{$question['key']} ?? '');

            return $question;
        }, self::registrationQuestions());
    }

    public static function activityTypeLabel(string $value): string
    {
        return match ($value) {
            'observasi' => 'Observasi',
            'riset' => 'Riset',
            'penugasan' => 'Penugasan',
            default => $value,
        };
    }

    /**
     * @return list<string>
     */
    public function normalizedActivityTypes(): array
    {
        return collect($this->activity_types ?? [])
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter(fn ($item) => in_array($item, self::ACTIVITY_TYPES, true))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function normalizedSuccessIndicators(): array
    {
        return collect($this->success_indicators ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->take(self::MAX_SUCCESS_INDICATORS)
            ->values()
            ->all();
    }

    public function hasParticipantSignature(): bool
    {
        return filled($this->participant_signature);
    }

    public function hasMentorSignature(): bool
    {
        return filled($this->mentor_signature);
    }

    public function activityTypeLabels(): string
    {
        $labels = array_map(
            fn (string $type) => self::activityTypeLabel($type),
            $this->normalizedActivityTypes()
        );

        return $labels === [] ? '—' : implode(', ', $labels);
    }

    public function generateLetterNumber(): string
    {
        return sprintf('IMM/%s/%04d', $this->created_at?->format('Y') ?? now()->format('Y'), $this->id);
    }

    public function cvUrl(): ?string
    {
        return $this->cv_path ? Storage::disk('public')->url($this->cv_path) : null;
    }

    /**
     * @return list<array{key: string, label: string, actor: string, done: bool, current: bool}>
     */
    public function approvalSteps(): array
    {
        $status = $this->status;

        return [
            [
                'key' => 'dosen',
                'label' => 'Dosen mengajukan',
                'actor' => 'Dosen',
                'done' => true,
                'current' => in_array($status, ['draft', 'revision'], true),
            ],
            [
                'key' => 'admin_review',
                'label' => 'Tinjauan admin',
                'actor' => 'Admin',
                'done' => in_array($status, ['waiting_mentor', 'waiting_admin', 'approved'], true),
                'current' => $status === 'submitted',
            ],
            [
                'key' => 'mentor',
                'label' => 'Persetujuan mentor',
                'actor' => 'Mentor',
                'done' => in_array($status, ['waiting_admin', 'approved'], true),
                'current' => $status === 'waiting_mentor',
            ],
            [
                'key' => 'admin_final',
                'label' => 'Pengesahan admin',
                'actor' => 'Admin',
                'done' => $status === 'approved',
                'current' => $status === 'waiting_admin',
            ],
            [
                'key' => 'user',
                'label' => 'Hasil ke dosen',
                'actor' => 'Dosen',
                'done' => in_array($status, ['approved', 'rejected'], true),
                'current' => false,
            ],
        ];
    }

    public function isAwaitingAdmin(): bool
    {
        return in_array($this->status, ['submitted', 'waiting_admin'], true);
    }

    public function isAwaitingMentor(): bool
    {
        return $this->status === 'waiting_mentor';
    }

    public function canBeRevisedByParticipant(): bool
    {
        return $this->status === 'revision';
    }

    public function currentStageLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Menunggu tinjauan admin',
            'waiting_mentor' => 'Menunggu persetujuan mentor',
            'waiting_admin' => 'Menunggu pengesahan admin',
            'approved' => 'Disetujui dan diteruskan ke dosen',
            'revision' => 'Perlu perbaikan oleh dosen',
            'rejected' => 'Ditolak',
            default => 'Draf pendaftaran',
        };
    }
}
