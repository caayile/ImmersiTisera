<?php

namespace App\Http\Controllers;

use App\Http\Resources\Api\OpportunityResource;
use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Participant;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OpportunityController extends Controller
{
    private const MAX_RESULTS = 100;

    private const LIST_CACHE_SECONDS = 30;

    public function __construct(private MatchingService $matching) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = BusinessUnit::query()
            ->select([
                'id', 'department_id', 'name', 'description', 'function', 'work_done',
                'example_activities', 'relevant_programs', 'status', 'registration_start',
                'registration_deadline', 'batch',
            ])
            ->selectSub(
                Department::query()->select('name')->whereColumn('departments.id', 'business_units.department_id'),
                'department_name',
            )
            ->selectSub(
                User::query()
                    ->join('mentors', 'users.id', '=', 'mentors.user_id')
                    ->select('users.id')
                    ->whereColumn('mentors.business_unit_id', 'business_units.id')
                    ->orderBy('mentors.id')
                    ->limit(1),
                'mentor_user_id',
            )
            ->selectSub(
                User::query()
                    ->join('mentors', 'users.id', '=', 'mentors.user_id')
                    ->select('users.name')
                    ->whereColumn('mentors.business_unit_id', 'business_units.id')
                    ->orderBy('mentors.id')
                    ->limit(1),
                'mentor_name',
            )
            ->withQuotaCount()
            ->orderByDesc('id')
            ->limit(self::MAX_RESULTS);

        $cacheScope = 'all';
        if ($user->isMentor()) {
            $mentor = $user->mentor;
            $query->where(function ($inner) use ($mentor) {
                $inner->where('id', $mentor?->business_unit_id)
                    ->orWhere('department_id', $mentor?->department_id);
            });
            $cacheScope = 'mentor:'.$user->id;
        }

        // Daftar statis di-cache sebagai atribut mentah agar tidak menyimpan model Eloquent.
        // Flag applied dan skor tetap dihitung per user di bawah.
        $cacheKey = 'api.opportunities.list.v1:'.$cacheScope;
        $rows = Cache::remember($cacheKey, now()->addSeconds(self::LIST_CACHE_SECONDS), function () use ($query) {
            return $query->get()
                ->map(fn (BusinessUnit $unit): array => $unit->getAttributes())
                ->all();
        });

        $units = collect($rows)->map(function (array $attributes): BusinessUnit {
            $unit = new BusinessUnit();
            $unit->exists = true;
            $unit->forceFill($attributes);

            return $unit;
        });

        // Profil peserta dipakai resource untuk skor kecocokan; cache singkat
        // menghindari query per request saat daftar sudah di-cache.
        $participantAttributes = Cache::remember(
            'api.participant.v1:'.$user->id,
            now()->addSeconds(self::LIST_CACHE_SECONDS),
            fn () => $user->participant?->getAttributes(),
        );

        $participant = null;
        if ($participantAttributes !== null) {
            $participant = new Participant();
            $participant->exists = true;
            $participant->forceFill($participantAttributes);
        }
        $user->setRelation('participant', $participant);

        // Satu query untuk semua unit, bukan satu query per unit di resource.
        if ($participant !== null && $units->isNotEmpty()) {
            $appliedUnitIds = Application::query()
                ->where('participant_id', $participant->id)
                ->whereIn('business_unit_id', $units->pluck('id'))
                ->pluck('business_unit_id')
                ->all();

            $appliedUnits = array_fill_keys($appliedUnitIds, true);

            $units->each(function (BusinessUnit $unit) use ($appliedUnits): void {
                $unit->setAttribute('applied_by_user', isset($appliedUnits[$unit->id]));
            });
        }

        return OpportunityResource::collection($units);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isMentor(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'problem' => ['required', 'string'],
            'opportunity' => ['required', 'string'],
            'needed_expertise' => ['required', 'array', 'min:1'],
            'expected_output' => ['required', 'string'],
            'purpose' => ['nullable', 'string', 'max:120'],
        ]);

        $mentor = $request->user()->mentor;
        abort_unless($mentor?->department_id, 422, 'Lengkapi profil mentor dan pilih unit bisnis.');

        $unit = $mentor->businessUnit ?: BusinessUnit::create([
            'department_id' => $mentor->department_id,
            'name' => $data['title'],
            'status' => 'open',
        ]);

        $unit->update([
            'name' => $data['title'],
            'description' => $data['problem'],
            'work_done' => $data['opportunity'],
            'function' => $data['purpose'] ?? $unit->function,
            'example_activities' => $data['expected_output'],
            'relevant_programs' => $data['needed_expertise'],
            'status' => 'open',
        ]);

        if (! $mentor->business_unit_id) {
            $mentor->update(['business_unit_id' => $unit->id]);
        }

        return (new OpportunityResource($unit->fresh(['department', 'mentors.user'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, BusinessUnit $opportunity)
    {
        return new OpportunityResource($opportunity);
    }
}
