<?php

namespace App\Support;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\Mentor;
use App\Models\MentorSession;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;

class ApiPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function user(User $user): array
    {
        $user->loadMissing(['participant', 'mentor.department', 'mentor.businessUnit']);

        $payload = $user->toApiUser();
        $payload['dosen_profile'] = $user->participant ? $this->dosenProfile($user->participant) : null;
        $payload['mentor_profile'] = $user->mentor ? $this->mentorProfile($user->mentor) : null;

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function dosenProfile(Participant $participant): array
    {
        $extra = $participant->profile_data ?? [];

        return [
            'nidn' => $participant->nidn,
            'prodi' => $participant->study_program,
            'department' => $participant->faculty,
            'expertise' => $participant->expertise ?? [],
            'interests' => $participant->competency ?? [],
            'experience' => $participant->experience,
            'purpose' => $extra['purpose'] ?? 'pembelajaran',
            'goals' => $participant->motivation,
            'competency_gap' => $extra['competency_gap'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mentorProfile(Mentor $mentor): array
    {
        return [
            'company_name' => $mentor->department?->name ?? 'TS Group',
            'industry_field' => $mentor->department?->name,
            'business_unit' => $mentor->businessUnit?->name,
            'department_function' => $mentor->businessUnit?->function,
            'job_title' => $mentor->position,
            'expertise' => $mentor->expertise ?? [],
            'industry_needs' => $mentor->businessUnit?->requirements,
            'problems' => $mentor->businessUnit?->description,
            'opportunities' => $mentor->businessUnit?->work_done,
            'dosen_needs' => $mentor->businessUnit?->example_activities,
            'availability' => $mentor->availability,
        ];
    }

    /**
     * @param  array{score?: int, warning?: bool}|null  $match
     * @return array<string, mixed>
     */
    public function opportunity(BusinessUnit $unit, ?array $match = null, bool $applied = false, ?Application $application = null): array
    {
        $unit->loadMissing(['department', 'mentors.user']);
        $mentor = $unit->mentors->first();
        $score = $match['score'] ?? 0;

        return [
            'id' => $unit->id,
            'title' => $unit->name,
            'field' => $unit->department?->name,
            'business_unit' => $unit->name,
            'purpose' => $unit->function ?: 'pembelajaran',
            'problem' => $unit->description,
            'opportunity' => $unit->work_done ?: $unit->function,
            'needed_expertise' => $unit->relevant_programs ?? [],
            'expected_output' => $unit->example_activities,
            'status' => $unit->status,
            'match_score' => $score,
            'match_label' => $this->matchLabel($score),
            'applied' => $applied,
            'application' => $application ? $this->application($application) : null,
            'mentor' => $mentor?->user ? [
                'id' => $mentor->user->id,
                'name' => $mentor->user->name,
                'company' => $unit->department?->name,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function application(Application $application): array
    {
        $application->loadMissing(['participant.user', 'businessUnit.department', 'mentor.user', 'program.agreement']);

        return [
            'id' => $application->id,
            'status' => $application->status,
            'letter_number' => $application->letter_number,
            'department' => $application->businessUnit?->department ? [
                'id' => $application->businessUnit->department->id,
                'name' => $application->businessUnit->department->name,
            ] : null,
            'business_unit' => $application->businessUnit ? [
                'id' => $application->businessUnit->id,
                'name' => $application->businessUnit->name,
            ] : null,
            'mentor' => $application->mentor?->user ? [
                'id' => $application->mentor->user->id,
                'name' => $application->mentor->user->name,
            ] : null,
            'match_score' => $application->match_score,
            'match_label' => $this->matchLabel((int) $application->match_score),
            'primary_activity' => $application->normalizedActivityTypes()[0] ?? null,
            'supporting_activity' => $application->normalizedActivityTypes()[1] ?? null,
            'proposed_shared_goal' => $application->shared_goal,
            'shared_goal' => $application->shared_goal,
            'activity_types' => $application->normalizedActivityTypes(),
            'problem_statement' => $application->problem_statement,
            'main_output' => $application->main_output,
            'participant_benefit' => $application->participant_benefit,
            'business_benefit' => $application->business_benefit,
            'success_indicators' => $application->normalizedSuccessIndicators(),
            'indicator_feedback' => $application->indicator_feedback,
            'mentor_note' => $application->mentor_note,
            'dosen' => $application->participant?->user ? [
                'id' => $application->participant->user->id,
                'name' => $application->participant->user->name,
                'email' => $application->participant->user->email,
            ] : null,
            'opportunity' => $application->businessUnit ? [
                'id' => $application->businessUnit->id,
                'title' => $application->businessUnit->name,
            ] : null,
            'agreement' => $application->program?->agreement
                ? ['id' => $application->program->agreement->id]
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function agreement(Agreement $agreement): array
    {
        $agreement->loadMissing(['program.participant.user', 'program.mentor.user', 'program.businessUnit']);
        $program = $agreement->program;

        return [
            'id' => $agreement->id,
            'status' => $agreement->status,
            'letter_number' => $agreement->letter_number,
            'letter_issued_at' => $agreement->letter_issued_at?->toIso8601String(),
            'shared_goal' => $agreement->objective,
            'problem_opportunity' => $agreement->problem_statement,
            'primary_activity' => $agreement->activities,
            'supporting_activity' => null,
            'promised_output' => $agreement->main_output,
            'benefit_dosen' => $agreement->participant_benefit,
            'benefit_industry' => $agreement->business_benefit,
            'success_indicator' => collect($agreement->success_indicators ?? [])->implode(', '),
            'potential_collaboration' => $agreement->collaboration_potential,
            'participant_signature' => $agreement->participant_signature,
            'mentor_signature' => $agreement->mentor_signature,
            'business_unit' => $program?->businessUnit?->name,
            'dosen' => $program?->participant?->user ? [
                'id' => $program->participant->user->id,
                'name' => $program->participant->user->name,
            ] : null,
            'mentor' => $program?->mentor?->user ? [
                'id' => $program->mentor->user->id,
                'name' => $program->mentor->user->name,
                'phone' => $program->mentor->user->phone,
            ] : null,
            'program' => $program ? ['id' => $program->id] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function program(Program $program, bool $full = false): array
    {
        $program->loadMissing([
            'participant.user',
            'mentor.user',
            'businessUnit',
            'agreement',
            'collaboration',
        ]);

        if ($full) {
            $program->loadMissing(['logbooks', 'mentorSessions', 'outputs', 'evaluations.evaluator', 'timelines']);
        }

        $avg = $program->relationLoaded('evaluations') && $program->evaluations->isNotEmpty()
            ? round($program->evaluations->avg(fn (Evaluation $item) => $item->average()), 1)
            : null;

        $payload = [
            'id' => $program->id,
            'status' => $program->status,
            'current_week' => $program->current_week,
            'computed_phase' => $this->phase((int) $program->current_week),
            'progress' => $program->progress,
            'collaboration_score' => $avg,
            'connect_decision' => $program->collaboration?->collaboration_type,
            'industry_insight' => $program->agreement?->problem_statement,
            'problem_statement' => $program->agreement?->problem_statement,
            'contribution_notes' => $program->agreement?->activities,
            'dosen' => $program->participant?->user ? [
                'id' => $program->participant->user->id,
                'name' => $program->participant->user->name,
                'email' => $program->participant->user->email,
            ] : null,
            'mentor' => $program->mentor?->user ? [
                'id' => $program->mentor->user->id,
                'name' => $program->mentor->user->name,
                'email' => $program->mentor->user->email,
            ] : null,
            'opportunity' => $program->businessUnit ? [
                'id' => $program->businessUnit->id,
                'title' => $program->businessUnit->name,
            ] : null,
            'agreement' => $program->agreement ? $this->agreement($program->agreement) : null,
        ];

        if ($full) {
            $payload['logbooks'] = $program->logbooks->map(fn (Logbook $item) => $this->logbook($item))->values();
            $payload['mentorings'] = $program->mentorSessions->map(fn (MentorSession $item) => [
                'id' => $item->id,
                'week' => $item->week,
                'session_date' => $item->session_date?->toDateString(),
                'found' => $item->findings,
                'working_on' => $item->current_work,
                'next_action' => $item->next_action,
                'mentor_feedback' => $item->feedback,
            ])->values();
            $payload['checkpoints'] = $program->timelines->map(fn ($item) => [
                'id' => $item->id,
                'week' => $item->week,
                'dosen_progress' => $item->description,
                'mentor_status' => $item->status === 'done' ? 'agree' : 'need_improvement',
                'mentor_notes' => $item->expected_output,
            ])->values();
            $payload['evaluations'] = $program->evaluations;
            $payload['outputs'] = $program->outputs;
            $payload['report'] = $program->outputs->firstWhere('is_final_report', true);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function logbook(Logbook $logbook): array
    {
        return [
            'id' => $logbook->id,
            'entry_date' => $logbook->date?->toDateString(),
            'what_did' => $logbook->what_i_did,
            'what_learned' => $logbook->what_i_learned,
            'what_found' => $logbook->what_i_found,
            'obstacles' => $logbook->value,
            'output' => $logbook->next_action,
            'activity' => $logbook->activity,
            'mentor_verified' => $logbook->status === 'approved',
            'status' => $logbook->status,
        ];
    }

    public function matchLabel(int $score): string
    {
        return match (true) {
            $score >= 70 => 'Sangat relevan',
            $score >= 50 => 'Cukup relevan',
            default => 'Kurang relevan',
        };
    }

    public function phase(int $week): string
    {
        return match (true) {
            $week <= 2 => 'discover',
            $week <= 4 => 'understand',
            $week <= 7 => 'contribute',
            default => 'deliver',
        };
    }
}
