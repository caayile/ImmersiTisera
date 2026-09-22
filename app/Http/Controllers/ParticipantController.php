<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Participant;
use App\Models\Program;
use App\Notifications\ImersiAlert;
use App\Services\ApplicationApprovalService;
use App\Support\Status;
use App\Support\StudyPrograms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParticipantController extends Controller
{
    public function dashboard(Request $request)
    {
        $program = $this->currentProgram($request);
        $program?->refreshProgress();
        $application = Application::with(['department', 'businessUnit', 'mentor.user'])
            ->where('participant_id', $request->user()->participant?->id)
            ->latest()
            ->first();

        $gradients = [
            'from-[#16352c] via-[#1f5a45] to-[#5ec69d]',
            'from-[#1e3a5f] via-[#2a6b55] to-[#7dd8b5]',
            'from-[#2f4a3c] via-[#3eaa84] to-[#a8e6cf]',
            'from-[#0f2a24] via-[#256b52] to-[#5ec69d]',
            'from-[#243d36] via-[#3e8f6d] to-[#8fd9b8]',
        ];

        $partners = Department::query()
            ->where('status', 'active')
            ->withCount(['businessUnits' => fn ($q) => $q->where('status', 'open')])
            ->orderBy('area')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function (Department $department, int $index) use ($gradients) {
                $imagePath = "images/partners/{$department->slug}.jpg";
                $imageExists = is_file(public_path($imagePath));

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'area' => $department->area ?: 'Mitra Imersi',
                    'description' => $department->description,
                    'slug' => $department->slug,
                    'url' => route('departments.show', $department),
                    'units' => $department->business_units_count,
                    'image' => $imageExists ? asset($imagePath) : null,
                    'gradient' => $gradients[$index % count($gradients)],
                ];
            });

        return view('participant.dashboard', [
            'participant' => $request->user()->participant,
            'program' => $program?->fresh(['department', 'businessUnit', 'mentor.user', 'agreement', 'logbooks', 'timelines']),
            'notifications' => $request->user()->unreadNotifications()->latest()->take(5)->get(),
            'partners' => $partners,
            'application' => $application,
        ]);
    }

    public function profile(Request $request)
    {
        $participant = $request->user()->participant ?? Participant::create(['user_id' => $request->user()->id]);

        return view('participant.profile', [
            'participant' => $participant,
            'studyProgramCatalog' => StudyPrograms::catalog(),
            'placementCatalog' => collect(StudyPrograms::allPrograms())
                ->mapWithKeys(fn (string $program) => [$program => StudyPrograms::placementTargets($program)])
                ->all(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nidn' => ['nullable', 'string', 'max:40'],
            'faculty' => ['required', 'string', Rule::in(StudyPrograms::faculties())],
            'study_program' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (! StudyPrograms::isValid((string) $request->input('faculty'), (string) $value)) {
                        $fail('Program studi tidak valid untuk fakultas yang dipilih.');
                    }
                },
            ],
            'expertise' => ['required', 'string'],
            'competency' => ['required', 'string'],
            'experience' => ['nullable', 'string'],
            'motivation' => ['nullable', 'string'],
        ]);

        $request->user()->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
        $request->user()->participant()->updateOrCreate(['user_id' => $request->user()->id], [
            'nidn' => $data['nidn'] ?? null,
            'faculty' => $data['faculty'],
            'study_program' => $data['study_program'],
            'expertise' => $this->csv($data['expertise']),
            'competency' => $this->csv($data['competency']),
            'experience' => $data['experience'] ?? null,
            'motivation' => $data['motivation'] ?? null,
        ]);

        return back()->with('status', 'Profil disimpan.');
    }

    public function applications(Request $request)
    {
        $apps = Application::with(['department', 'businessUnit', 'mentor.user'])
            ->where('participant_id', $request->user()->participant?->id)
            ->latest()
            ->get();

        return view('participant.applications', compact('apps'));
    }

    public function createApplication(Request $request)
    {
        $participant = $request->user()->participant;
        if (! $participant?->study_program) {
            return redirect()->route('participant.profile')->with('status', 'Lengkapi profil sebelum mendaftar program.');
        }

        $unit = BusinessUnit::with(['department', 'mentors.user'])->find($request->integer('unit'));
        if (! $unit || $unit->status !== 'open') {
            return redirect()->route('departments.index')->with('status', 'Pilih unit bisnis atau departemen terlebih dahulu.');
        }

        return view('participant.application-form', [
            'participant' => $participant,
            'unit' => $unit,
            'application' => null,
            'periodStart' => now()->toDateString(),
            'periodEnd' => Application::periodEndFromStart(now())->toDateString(),
        ]);
    }

    public function storeApplication(Request $request, ApplicationApprovalService $approvals)
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'exists:business_units,id'],
            ...$this->registrationFieldRules($request),
        ]);

        $participant = $request->user()->participant;
        if (! $participant?->study_program) {
            return redirect()->route('participant.profile')->with('status', 'Lengkapi profil sebelum mendaftar program.');
        }

        $data['cv_path'] = $request->file('cv')->store('application-cvs', 'public');

        $unit = BusinessUnit::with('mentors')->findOrFail($data['business_unit_id']);
        $application = $approvals->submit($participant, $unit, $data);

        return redirect()
            ->route('participant.applications.show', $application)
            ->with('status', 'Pendaftaran dan surat persetujuan terkirim ke admin.');
    }

    public function showApplication(Request $request, Application $application)
    {
        $this->authorizeApplication($request, $application);

        return view('participant.application-show', [
            'application' => $application->load(['participant.user', 'department', 'businessUnit', 'mentor.user', 'program']),
        ]);
    }

    public function editApplication(Request $request, Application $application)
    {
        $this->authorizeApplication($request, $application);
        abort_unless($application->canBeRevisedByParticipant(), 403);

        $start = $application->period_start ?? now();

        return view('participant.application-form', [
            'participant' => $request->user()->participant,
            'unit' => $application->businessUnit()->with('department')->first(),
            'application' => $application,
            'periodStart' => $start->toDateString(),
            'periodEnd' => ($application->period_end ?? Application::periodEndFromStart($start))->toDateString(),
        ]);
    }

    public function updateApplication(Request $request, Application $application, ApplicationApprovalService $approvals)
    {
        $this->authorizeApplication($request, $application);

        $data = $request->validate($this->registrationFieldRules($request, $application));
        $data['business_unit_id'] = $application->business_unit_id;

        if ($request->hasFile('cv')) {
            if ($application->cv_path) {
                Storage::disk('public')->delete($application->cv_path);
            }

            $data['cv_path'] = $request->file('cv')->store('application-cvs', 'public');
        }

        $approvals->resubmit($application, $data);

        return redirect()
            ->route('participant.applications.show', $application)
            ->with('status', 'Pendaftaran dikirim ulang ke admin.');
    }

    public function program(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.program', compact('program'));
    }

    public function agreement(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.agreement', ['program' => $program, 'agreement' => $program?->agreement]);
    }

    public function updateAgreement(Request $request)
    {
        $program = $this->currentProgram($request, true);
        $agreement = $program->agreement;
        abort_unless($program && $agreement && in_array($agreement->status, ['draft', 'revision'], true), 403);

        $data = $request->validate([
            'objective' => ['required', 'string'],
            'problem_statement' => ['required', 'string'],
            'activities' => ['required', 'string'],
            'main_output' => ['required', 'string'],
            'participant_benefit' => ['required', 'string'],
            'business_benefit' => ['required', 'string'],
            'success_indicators' => ['required', 'array', 'max:3'],
            'collaboration_potential' => ['nullable', 'string'],
        ]);

        $agreement->update([...$data, 'status' => 'submitted', 'participant_approved_at' => now()]);
        $program->mentor->user->notify(new ImersiAlert('Agreement diajukan', 'Menunggu persetujuan mentor.', route('mentor.agreements')));

        return back()->with('status', 'Agreement diajukan ke mentor.');
    }

    public function timeline(Request $request)
    {
        return view('participant.timeline', ['program' => $this->currentProgram($request)]);
    }

    public function logbooks(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.logbooks', compact('program'));
    }

    public function storeLogbook(Request $request)
    {
        $program = $this->currentProgram($request, true);
        abort_unless($program && $program->status === 'active', 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'activity' => ['required', 'string', 'max:180'],
            'what_i_did' => ['required', 'string'],
            'what_i_learned' => ['required', 'string'],
            'what_i_found' => ['required', 'string'],
            'value' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $path = $request->file('attachment')?->store('logbooks', 'public');
        $program->logbooks()->create([
            ...collect($data)->except('attachment')->toArray(),
            'participant_id' => $program->participant_id,
            'attachment_path' => $path,
            'status' => 'submitted',
        ]);
        $program->mentor->user->notify(new ImersiAlert('Logbook baru', 'Ada logbook menunggu review.', route('mentor.logbooks')));

        return back()->with('status', 'Logbook dikirim.');
    }

    public function mentoring(Request $request)
    {
        return view('participant.mentoring', ['program' => $this->currentProgram($request)]);
    }

    public function outputs(Request $request)
    {
        return view('participant.outputs', [
            'program' => $this->currentProgram($request),
            'types' => Status::OUTPUT_TYPES,
        ]);
    }

    public function storeOutput(Request $request)
    {
        $program = $this->currentProgram($request);
        abort_unless($program, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_main_output' => ['sometimes', 'boolean'],
            'is_final_report' => ['sometimes', 'boolean'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx,zip'],
        ]);

        if ($request->boolean('is_main_output')) {
            $program->outputs()->update(['is_main_output' => false]);
        }

        $program->outputs()->create([
            'participant_id' => $program->participant_id,
            'title' => $data['title'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'is_main_output' => $request->boolean('is_main_output'),
            'is_final_report' => $request->boolean('is_final_report'),
            'file_path' => $request->file('file')?->store('outputs', 'public'),
            'status' => 'submitted',
        ]);
        $program->mentor->user->notify(new ImersiAlert('Output dikirim', $data['title'].' menunggu validasi.', route('mentor.outputs')));

        return back()->with('status', 'Output dikirim.');
    }

    public function evaluation(Request $request)
    {
        $program = $this->currentProgram($request);
        $mine = $program?->evaluations()->where('evaluator_id', $request->user()->id)->first();

        return view('participant.evaluation', compact('program', 'mine'));
    }

    public function storeEvaluation(Request $request)
    {
        $program = $this->currentProgram($request);
        abort_unless($program, 404);
        $data = $this->evalRules($request);
        Evaluation::updateOrCreate(
            ['program_id' => $program->id, 'evaluator_id' => $request->user()->id],
            $data
        );

        return back()->with('status', 'Evaluasi tersimpan.');
    }

    public function finalReport(Request $request)
    {
        return view('participant.final-report', [
            'program' => $this->currentProgram($request),
        ]);
    }

    public function collaboration(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.collaboration', compact('program'));
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $request->user()->unreadNotifications->markAsRead();

        return view('participant.notifications', compact('notifications'));
    }

    public function settings()
    {
        return view('participant.settings');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Kata sandi diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationFieldRules(Request $request, ?Application $application = null): array
    {
        $rules = [];

        foreach (Application::registrationQuestionKeys() as $key) {
            $rules[$key] = ['required', 'string', 'min:20'];
        }

        return [
            ...$rules,
            'cv' => [
                $application?->cv_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],
            'period_start' => ['required', 'date'],
            'period_end' => [
                'required',
                'date',
                'after:period_start',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $start = $request->date('period_start');
                    $end = $request->date('period_end');
                    if ($start && $end && ! Application::isTwoMonthPeriod($start, $end)) {
                        $fail('Periode harus tepat 2 bulan.');
                    }
                },
            ],
            'declaration' => ['accepted'],
        ];
    }

    private function authorizeApplication(Request $request, Application $application): void
    {
        abort_unless($application->participant_id === $request->user()->participant?->id, 403);
    }

    private function currentProgram(Request $request, bool $required = false): ?Program
    {
        $program = Program::with([
            'department', 'businessUnit', 'mentor.user', 'participant.user', 'agreement',
            'timelines', 'logbooks', 'mentorSessions', 'outputs', 'evaluations.evaluator', 'collaboration',
        ])->where('participant_id', $request->user()->participant?->id)->latest()->first();

        abort_if($required && ! $program, 404, 'Belum ada program aktif.');

        return $program;
    }

    private function csv(string $value): array
    {
        return collect(explode(',', $value))->map(fn ($item) => trim($item))->filter()->values()->all();
    }

    private function evalRules(Request $request): array
    {
        return $request->validate([
            'industry_understanding' => ['required', 'integer', 'min:1', 'max:5'],
            'relationship' => ['required', 'integer', 'min:1', 'max:5'],
            'output' => ['required', 'integer', 'min:1', 'max:5'],
            'mutual_benefit' => ['required', 'integer', 'min:1', 'max:5'],
            'collaboration_potential' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string'],
        ]);
    }
}
