<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\Participant;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Notifications\ImersiAlert;
use App\Services\MatchingService;
use App\Support\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParticipantController extends Controller
{
    public function dashboard(Request $request)
    {
        $program = $this->currentProgram($request);
        $program?->refreshProgress();

        return view('participant.dashboard', [
            'participant' => $request->user()->participant,
            'program' => $program?->fresh(['department', 'businessUnit', 'mentor.user', 'agreement', 'logbooks', 'timelines']),
            'notifications' => $request->user()->unreadNotifications()->latest()->take(5)->get(),
        ]);
    }

    public function profile(Request $request)
    {
        $participant = $request->user()->participant ?? Participant::create(['user_id' => $request->user()->id]);

        return view('participant.profile', compact('participant'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nidn' => ['nullable', 'string', 'max:40'],
            'study_program' => ['required', 'string', 'max:120'],
            'expertise' => ['required', 'string'],
            'competency' => ['required', 'string'],
            'experience' => ['nullable', 'string'],
            'motivation' => ['nullable', 'string'],
        ]);

        $request->user()->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
        $request->user()->participant()->updateOrCreate(['user_id' => $request->user()->id], [
            'nidn' => $data['nidn'] ?? null,
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
        $departments = Department::with(['businessUnits' => fn ($q) => $q->where('status', 'open')])->where('status', 'active')->get();
        $prefill = $request->integer('unit');

        return view('participant.application-form', compact('departments', 'prefill'));
    }

    public function storeApplication(Request $request, MatchingService $matching)
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'motivation' => ['required', 'string'],
            'preferred_period' => ['nullable', 'string', 'max:80'],
        ]);

        $participant = $request->user()->participant;
        abort_unless($participant?->study_program, 422, 'Lengkapi profil terlebih dahulu.');

        $unit = BusinessUnit::with('mentors')->findOrFail($data['business_unit_id']);
        $match = $matching->score($participant, $unit);
        $mentor = $unit->mentors()->first();

        $application = Application::create([
            'participant_id' => $participant->id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'mentor_id' => $mentor?->id,
            'motivation' => $data['motivation'],
            'preferred_period' => $data['preferred_period'] ?? null,
            'match_score' => $match['score'],
            'relevance_warning' => $match['warning'],
            'status' => 'submitted',
        ]);

        if ($mentor?->user) {
            $mentor->user->notify(new ImersiAlert('Pengajuan baru', $request->user()->name.' mengajukan program di '.$unit->name, route('mentor.participants')));
        }

        return redirect()->route('participant.applications')->with('status', 'Pengajuan terkirim. Skor matching '.$match['score'].'%.');
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
