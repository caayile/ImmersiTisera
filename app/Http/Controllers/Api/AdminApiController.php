<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DepartmentNeedResource;
use App\Http\Resources\Api\ProgramResource;
use App\Http\Resources\Api\UserResource;
use App\Models\DepartmentNeed;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminApiController extends Controller
{
    public function overview(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $now = now()->toDateTimeString();
        $counts = DB::query()
            ->selectRaw('(select count(*) from users where verification_status = ?) as pending_verification', ['pending_verification'])
            ->selectRaw('(select count(*) from participants) as dosen')
            ->selectRaw('(select count(*) from mentors) as mentors')
            ->selectRaw("(select count(*) from programs where status = 'active') as active_programs")
            ->selectRaw('(select count(*) from business_units where status = ? and (registration_start is null or registration_start <= ?) and (registration_deadline is null or registration_deadline >= ?)) as opportunities', ['open', $now, $now])
            ->selectRaw('(select count(*) from department_needs) as department_needs')
            ->first();

        return response()->json([
            'pending_verification' => (int) ($counts->pending_verification ?? 0),
            'dosen' => (int) ($counts->dosen ?? 0),
            'mentors' => (int) ($counts->mentors ?? 0),
            'active_programs' => (int) ($counts->active_programs ?? 0),
            'opportunities' => (int) ($counts->opportunities ?? 0),
            'department_needs' => (int) ($counts->department_needs ?? 0),
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
