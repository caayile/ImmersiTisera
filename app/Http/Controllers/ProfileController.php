<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Mentor;
use App\Models\Participant;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ApiPresenter $presenter) {}

    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->isParticipant()) {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'nidn' => ['nullable', 'string', 'max:40'],
                'prodi' => ['required', 'string', 'max:120'],
                'department' => ['nullable', 'string', 'max:120'],
                'expertise' => ['required', 'array', 'min:1'],
                'interests' => ['required', 'array', 'min:1'],
                'experience' => ['required', 'string'],
                'purpose' => ['nullable', 'string', 'max:120'],
                'goals' => ['required', 'string'],
                'competency_gap' => ['nullable', 'string'],
            ]);

            $user->update([
                'name' => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
            ]);

            $existing = $user->participant?->profile_data ?? [];

            Participant::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nidn' => $data['nidn'] ?? null,
                    'faculty' => $data['department'] ?? null,
                    'study_program' => $data['prodi'],
                    'expertise' => $data['expertise'],
                    'competency' => $data['interests'],
                    'experience' => $data['experience'],
                    'motivation' => $data['goals'],
                    'profile_data' => array_merge($existing, [
                        'purpose' => $data['purpose'] ?? ($existing['purpose'] ?? 'pembelajaran'),
                        'competency_gap' => $data['competency_gap'] ?? ($existing['competency_gap'] ?? null),
                    ]),
                ]
            );
        } elseif ($user->isMentor()) {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'company_name' => ['nullable', 'string', 'max:180'],
                'industry_field' => ['nullable', 'string', 'max:120'],
                'business_unit' => ['required', 'string', 'max:120'],
                'department_function' => ['nullable', 'string', 'max:120'],
                'job_title' => ['nullable', 'string', 'max:120'],
                'expertise' => ['required', 'array', 'min:1'],
                'industry_needs' => ['nullable', 'string'],
                'problems' => ['nullable', 'string'],
                'opportunities' => ['nullable', 'string'],
                'dosen_needs' => ['nullable', 'string'],
                'availability' => ['nullable', 'string', 'max:120'],
            ]);

            $user->update([
                'name' => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
            ]);

            $unit = BusinessUnit::query()->where('name', $data['business_unit'])->first();
            $department = $unit?->department
                ?: Department::query()->where('name', $data['industry_field'] ?? '')->first();

            Mentor::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department?->id ?? $user->mentor?->department_id,
                    'business_unit_id' => $unit?->id ?? $user->mentor?->business_unit_id,
                    'position' => $data['job_title'] ?? null,
                    'expertise' => $data['expertise'],
                    'availability' => $data['availability'] ?? null,
                ]
            );

            if ($unit) {
                $unit->update([
                    'function' => $data['department_function'] ?? $unit->function,
                    'requirements' => $data['industry_needs'] ?? $unit->requirements,
                    'description' => $data['problems'] ?? $unit->description,
                    'work_done' => $data['opportunities'] ?? $unit->work_done,
                    'example_activities' => $data['dosen_needs'] ?? $unit->example_activities,
                ]);
            }
        }

        return response()->json($this->presenter->user($user->fresh(['participant', 'mentor.department', 'mentor.businessUnit'])));
    }
}
