<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'user') {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'nidn' => ['nullable', 'string', 'max:30'],
                'prodi' => ['required', 'string', 'max:120'],
                'department' => ['nullable', 'string', 'max:120'],
                'expertise' => ['required', 'array', 'min:1'],
                'interests' => ['required', 'array', 'min:1'],
                'experience' => ['required', 'string'],
                'purpose' => ['required', 'in:riset,observasi,pembelajaran,penugasan'],
                'goals' => ['required', 'string'],
                'competency_gap' => ['required', 'string'],
            ]);

            $user->update([
                'name' => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
                'verification_status' => $user->verification_status === 'rejected'
                    ? 'pending_verification'
                    : ($user->verification_status === 'verified' ? 'verified' : 'pending_verification'),
                'rejection_reason' => null,
            ]);

            $user->dosenProfile()->updateOrCreate(
                ['user_id' => $user->id],
                collect($data)->only([
                    'nidn', 'prodi', 'department', 'expertise', 'interests',
                    'experience', 'purpose', 'goals', 'competency_gap',
                ])->toArray()
            );
        } elseif ($user->role === 'mentor') {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'company_name' => ['required', 'string', 'max:180'],
                'industry_field' => ['required', 'string', 'max:120'],
                'business_unit' => ['required', 'string', 'max:120'],
                'department_function' => ['nullable', 'string', 'max:120'],
                'job_title' => ['nullable', 'string', 'max:120'],
                'expertise' => ['required', 'array', 'min:1'],
                'industry_needs' => ['required', 'string'],
                'problems' => ['required', 'string'],
                'opportunities' => ['required', 'string'],
                'dosen_needs' => ['required', 'string'],
                'availability' => ['nullable', 'string', 'max:120'],
            ]);

            $user->update([
                'name' => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
                'verification_status' => $user->verification_status === 'rejected'
                    ? 'pending_verification'
                    : ($user->verification_status === 'verified' ? 'verified' : 'pending_verification'),
                'rejection_reason' => null,
            ]);

            $user->mentorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                collect($data)->only([
                    'company_name', 'industry_field', 'business_unit', 'department_function',
                    'job_title', 'expertise', 'industry_needs', 'problems', 'opportunities',
                    'dosen_needs', 'availability',
                ])->toArray()
            );
        }

        return response()->json($user->fresh(['dosenProfile', 'mentorProfile'])->toApiArray());
    }
}
