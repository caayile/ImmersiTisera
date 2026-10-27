<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DepartmentNeedResource;
use App\Http\Resources\Api\ProgramResource;
use App\Http\Resources\Api\UserResource;
use App\Models\BusinessUnit;
use App\Models\DepartmentNeed;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function overview(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json([
            'pending_verification' => User::query()->where('verification_status', 'pending_verification')->count(),
            'dosen' => Participant::count(),
            'mentors' => Mentor::count(),
            'active_programs' => Program::query()->where('status', 'active')->count(),
            'opportunities' => BusinessUnit::query()->currentlyOpen()->count(),
            'department_needs' => DepartmentNeed::count(),
        ]);
    }

    public function users(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $status = $request->string('status')->toString();
        $query = User::query()->with(['participant', 'mentor.department', 'mentor.businessUnit'])->latest('id');

        if ($status !== '') {
            $query->where('verification_status', $status);
        }

        return UserResource::collection($query->get());
    }

    public function verify(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user->update(['verification_status' => 'verified', 'rejection_reason' => null]);

        return new UserResource($user->fresh(['participant', 'mentor.department', 'mentor.businessUnit']));
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate(['reason' => ['required', 'string']]);
        $user->update(['verification_status' => 'rejected', 'rejection_reason' => $data['reason']]);

        return new UserResource($user->fresh(['participant', 'mentor.department', 'mentor.businessUnit']));
    }

    public function programs(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $programs = Program::query()
            ->with(['participant.user', 'mentor.user', 'businessUnit', 'agreement', 'collaboration'])
            ->latest('id')
            ->get();

        return ProgramResource::collection($programs);
    }

    public function needs(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return DepartmentNeedResource::collection(DepartmentNeed::query()->latest('id')->get());
    }

    public function storeNeed(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'prodi' => ['required', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'purpose' => ['required', 'string', 'max:80'],
            'academic_needs' => ['required', 'string'],
            'problem' => ['nullable', 'string'],
            'goal' => ['nullable', 'string'],
        ]);

        $need = DepartmentNeed::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return (new DepartmentNeedResource($need))
            ->response()
            ->setStatusCode(201);
    }
}
