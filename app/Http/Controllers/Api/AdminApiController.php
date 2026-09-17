<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\DepartmentNeed;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function __construct(private ApiPresenter $presenter) {}

    public function overview(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json([
            'pending_verification' => User::query()->where('verification_status', 'pending_verification')->count(),
            'dosen' => Participant::count(),
            'mentors' => Mentor::count(),
            'active_programs' => Program::query()->where('status', 'active')->count(),
            'opportunities' => BusinessUnit::query()->where('status', 'open')->count(),
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

        return response()->json($query->get()->map(fn (User $user) => $this->presenter->user($user)));
    }

    public function verify(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user->update(['verification_status' => 'verified', 'rejection_reason' => null]);

        return response()->json($this->presenter->user($user->fresh(['participant', 'mentor.department', 'mentor.businessUnit'])));
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate(['reason' => ['required', 'string']]);
        $user->update(['verification_status' => 'rejected', 'rejection_reason' => $data['reason']]);

        return response()->json($this->presenter->user($user->fresh(['participant', 'mentor.department', 'mentor.businessUnit'])));
    }

    public function programs(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $programs = Program::query()
            ->with(['participant.user', 'mentor.user', 'businessUnit', 'agreement', 'collaboration'])
            ->latest('id')
            ->get();

        return response()->json($programs->map(fn (Program $program) => $this->presenter->program($program)));
    }

    public function needs(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json(DepartmentNeed::query()->latest('id')->get());
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

        return response()->json($need, 201);
    }
}
