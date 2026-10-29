<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->loadMissing([
            'participant',
            'mentor.department',
            'mentor.businessUnit',
        ]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatarUrl(),
            'role' => $this->isParticipant() ? 'user' : $this->role,
            'status' => $this->status ?? 'active',
            'verification_status' => $this->verification_status ?? 'verified',
            'phone' => $this->phone,
            'home' => route($this->homeRoute()),
            'dosen_profile' => $this->participant ? $this->dosenProfile() : null,
            'mentor_profile' => $this->mentor ? $this->mentorProfile() : null,
        ];
    }

    private function dosenProfile(): array
    {
        $participant = $this->participant;
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

    private function mentorProfile(): array
    {
        $mentor = $this->mentor;

        return [
            'company_name' => optional($mentor->department)->name ?? 'TS Group',
            'industry_field' => optional($mentor->department)->name,
            'business_unit' => optional($mentor->businessUnit)->name,
            'department_function' => optional($mentor->businessUnit)->function,
            'job_title' => $mentor->position,
            'expertise' => $mentor->expertise ?? [],
            'industry_needs' => optional($mentor->businessUnit)->requirements,
            'problems' => optional($mentor->businessUnit)->description,
            'opportunities' => optional($mentor->businessUnit)->work_done,
            'dosen_needs' => optional($mentor->businessUnit)->example_activities,
        ];
    }
}
