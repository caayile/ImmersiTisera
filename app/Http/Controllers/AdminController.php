<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\CollaborationPipeline;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Models\Logbook;
use App\Models\Mentor;
use App\Models\News;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Notifications\ImersiAlert;
use App\Support\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $programs = Program::query();

        return view('admin.dashboard', [
            'stats' => [
                'Peserta' => Participant::count(),
                'Mentor' => Mentor::count(),
                'Department' => Department::count(),
                'Unit Bisnis' => BusinessUnit::count(),
                'Program aktif' => Program::where('status', 'active')->count(),
                'Completed' => Program::where('status', 'completed')->count(),
                'Pending approval' => Application::where('status', 'submitted')->count() + Agreement::where('status', 'submitted')->count(),
                'Dengan output' => Program::whereHas('outputs')->count(),
            ],
            'statusCounts' => Program::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'logbookCompliance' => Program::where('status', 'active')->count()
                ? round(Logbook::where('status', 'approved')->count() / max(1, Program::where('status', 'active')->count()) * 10)
                : 0,
            'collab' => CollaborationPipeline::selectRaw('level, count(*) as total')->groupBy('level')->pluck('total', 'level'),
        ]);
    }

    public function users()
    {
        return view('admin.users', ['users' => User::latest()->paginate(20)]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', Rule::in(['participant', 'mentor', 'admin'])],
        ]);
        $user = User::create([...$data, 'status' => 'active', 'verification_status' => 'verified']);
        if ($user->role === 'participant') {
            Participant::create(['user_id' => $user->id]);
        }
        if ($user->role === 'mentor') {
            Mentor::create(['user_id' => $user->id]);
        }

        return back()->with('status', 'User dibuat.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'role' => ['required', Rule::in(['participant', 'mentor', 'admin'])],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);
        $user->update($data);

        return back()->with('status', 'User diperbarui.');
    }

    public function departments()
    {
        return view('admin.departments', ['departments' => Department::withCount('businessUnits')->get()]);
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'function' => ['nullable', 'string'],
            'area' => ['nullable', 'string'],
        ]);
        Department::create([...$data, 'slug' => str($data['name'])->slug(), 'status' => 'active']);

        return back()->with('status', 'Unit bisnis dibuat.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'function' => ['nullable', 'string'],
            'area' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);
        $department->update([
            ...$data,
            'slug' => Str::slug($data['name']),
        ]);

        return back()->with('status', 'Unit bisnis diperbarui.');
    }

    public function units()
    {
        return view('admin.units', [
            'units' => BusinessUnit::with(['department', 'mentors.user'])->get(),
            'departments' => Department::orderBy('name')->get(),
            'mentors' => Mentor::with('user')->get(),
        ]);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'function' => ['nullable', 'string'],
            'work_done' => ['nullable', 'string'],
            'example_activities' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'relevant_programs' => ['nullable', 'string'],
            'period' => ['nullable', 'string', 'max:120'],
            'mentor_id' => ['nullable', 'exists:mentors,id'],
        ]);
        $unit = BusinessUnit::create([
            ...collect($data)->except('mentor_id', 'relevant_programs')->toArray(),
            'relevant_programs' => $this->parseRelevantPrograms($data['relevant_programs'] ?? null),
            'status' => 'open',
        ]);
        if ($request->mentor_id) {
            Mentor::where('id', $request->mentor_id)->update([
                'business_unit_id' => $unit->id,
                'department_id' => $unit->department_id,
            ]);
        }

        return back()->with('status', 'Departemen dibuat.');
    }

    public function updateUnit(Request $request, BusinessUnit $businessUnit)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'function' => ['nullable', 'string'],
            'work_done' => ['nullable', 'string'],
            'example_activities' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'relevant_programs' => ['nullable', 'string'],
            'period' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'mentor_id' => ['nullable', 'exists:mentors,id'],
        ]);
        $businessUnit->update([
            ...collect($data)->except('mentor_id', 'relevant_programs')->toArray(),
            'relevant_programs' => $this->parseRelevantPrograms($data['relevant_programs'] ?? null),
        ]);
        if ($request->mentor_id) {
            Mentor::where('id', $request->mentor_id)->update([
                'business_unit_id' => $businessUnit->id,
                'department_id' => $businessUnit->department_id,
            ]);
        }

        return back()->with('status', 'Departemen diperbarui.');
    }

    private function parseRelevantPrograms(?string $programs): array
    {
        return collect(explode(',', (string) $programs))
            ->map(fn (string $program): string => trim($program))
            ->filter()
            ->values()
            ->all();
    }

    public function mentors()
    {
        return view('admin.mentors', ['mentors' => Mentor::with(['user', 'department', 'businessUnit'])->get()]);
    }

    public function participants()
    {
        return view('admin.participants', ['participants' => Participant::with('user')->latest()->get()]);
    }

    public function programs(Request $request)
    {
        $query = Program::with(['participant.user', 'mentor.user', 'department', 'businessUnit', 'agreement']);
        foreach (['status', 'department_id', 'business_unit_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        return view('admin.programs', [
            'programs' => $query->latest()->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function matching()
    {
        return view('admin.matching', [
            'applications' => Application::with(['participant.user', 'department', 'businessUnit', 'mentor.user'])->latest()->get(),
            'mentors' => Mentor::with(['user', 'businessUnit'])->get(),
            'units' => BusinessUnit::with('department')->get(),
        ]);
    }

    public function updateMatching(Request $request, Application $application)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'revision'])],
            'mentor_id' => ['nullable', 'exists:mentors,id'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'matching_notes' => ['nullable', 'string'],
        ]);

        if ($data['business_unit_id'] ?? null) {
            $unit = BusinessUnit::find($data['business_unit_id']);
            $application->business_unit_id = $unit->id;
            $application->department_id = $unit->department_id;
        }
        $application->fill($data)->save();

        if ($data['status'] === 'approved') {
            $program = Program::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'participant_id' => $application->participant_id,
                    'mentor_id' => $application->mentor_id,
                    'department_id' => $application->department_id,
                    'business_unit_id' => $application->business_unit_id,
                    'status' => 'submitted',
                ]
            );
            $program->agreement()->firstOrCreate(['program_id' => $program->id], ['status' => 'draft']);
            $application->participant->user->notify(new ImersiAlert('Matching disetujui', 'Lanjutkan ke Industry Immersion Agreement.', route('participant.agreement')));
        } else {
            $application->participant->user->notify(new ImersiAlert('Update matching', 'Status: '.$data['status'], route('participant.applications')));
        }

        return back()->with('status', 'Matching diperbarui.');
    }

    public function agreements()
    {
        return view('admin.agreements', ['agreements' => Agreement::with('program.participant.user')->latest()->get()]);
    }

    public function monitoring()
    {
        $programs = Program::with(['participant.user', 'mentor.user', 'logbooks', 'outputs', 'evaluations', 'collaboration', 'agreement'])->latest()->get();

        return view('admin.monitoring', compact('programs'));
    }

    public function evaluations()
    {
        return view('admin.evaluations', ['evaluations' => Evaluation::with(['program.participant.user', 'evaluator'])->latest()->get()]);
    }

    public function collaborations()
    {
        return view('admin.collaborations', [
            'items' => CollaborationPipeline::with('program.participant.user')->latest()->get(),
            'levels' => Status::COLLABORATION_LEVELS,
        ]);
    }

    public function reports(Request $request)
    {
        $programs = Program::with(['participant.user', 'mentor.user', 'department', 'businessUnit'])->latest()->get();

        if ($request->get('export') === 'csv') {
            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, ['Program', 'Peserta', 'Mentor', 'Department', 'Unit', 'Status']);
            foreach ($programs as $program) {
                fputcsv($csv, [
                    $program->id,
                    $program->participant->user->name,
                    $program->mentor->user->name,
                    $program->department->name,
                    $program->businessUnit->name,
                    $program->status,
                ]);
            }
            rewind($csv);

            return Response::streamDownload(function () use ($csv) {
                echo stream_get_contents($csv);
            }, 'imersi-programs.csv');
        }

        return view('admin.reports', compact('programs'));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function departmentHero()
    {
        return view('admin.department-hero', [
            'setting' => HeroSetting::forPage('departments'),
            'slides' => HeroSlide::forPage('departments')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function updateDepartmentHeroBackground(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'background' => ['nullable', 'image', 'max:5120'],
        ]);

        $setting = HeroSetting::forPage('departments');
        $payload = [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
        ];

        if ($request->hasFile('background')) {
            $payload['background_path'] = $this->storeHeroUpload($request->file('background'), $setting->background_path);
        }

        $setting->update($payload);

        return back()->with('status', 'Background hero departemen diperbarui.');
    }

    public function storeDepartmentHeroSlide(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        HeroSlide::create([
            'page' => 'departments',
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? (HeroSlide::forPage('departments')->max('sort_order') + 1),
            'image_path' => $this->storeHeroUpload($request->file('image')),
            'is_active' => true,
        ]);

        return back()->with('status', 'Slide hero ditambahkan.');
    }

    public function updateDepartmentHeroSlide(Request $request, HeroSlide $heroSlide)
    {
        abort_unless($heroSlide->page === 'departments', 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $payload = [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? $heroSlide->sort_order,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $this->storeHeroUpload($request->file('image'), $heroSlide->image_path);
        }

        $heroSlide->update($payload);

        return back()->with('status', 'Slide hero diperbarui.');
    }

    public function destroyDepartmentHeroSlide(HeroSlide $heroSlide)
    {
        abort_unless($heroSlide->page === 'departments', 404);

        $this->deleteHeroUpload($heroSlide->image_path);
        $heroSlide->delete();

        return back()->with('status', 'Slide hero dihapus.');
    }

    private function storeHeroUpload($file, ?string $previous = null): string
    {
        $this->deleteHeroUpload($previous);

        return $file->store('hero', 'public');
    }

    private function deleteHeroUpload(?string $path): void
    {
        if (! $path || str_starts_with($path, 'images/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function news()
    {
        return view('admin.news', [
            'items' => News::with('author')->latest('published_at')->latest('id')->paginate(15),
        ]);
    }

    public function storeNews(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);

        News::create([
            ...$data,
            'slug' => News::makeSlug($data['title']),
            'category' => $data['category'] ?: 'Umum',
            'excerpt' => $data['excerpt'] ?: Str::limit(strip_tags($data['body']), 160),
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] ?? now())
                : ($data['published_at'] ?? null),
            'author_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Berita ditambahkan.');
    }

    public function updateNews(Request $request, News $news)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);

        $news->update([
            ...$data,
            'slug' => News::makeSlug($data['title'], $news->id),
            'category' => $data['category'] ?: 'Umum',
            'excerpt' => $data['excerpt'] ?: Str::limit(strip_tags($data['body']), 160),
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] ?? $news->published_at ?? now())
                : ($data['published_at'] ?? null),
        ]);

        return back()->with('status', 'Berita diperbarui.');
    }

    public function destroyNews(News $news)
    {
        $news->delete();

        return back()->with('status', 'Berita dihapus.');
    }

    public function completeProgram(Program $program)
    {
        abort_unless($program->canComplete(), 422, 'Main output, final report, dan evaluasi harus selesai.');
        $program->update(['status' => 'completed', 'progress' => 100]);
        $program->participant->user->notify(new ImersiAlert('Program completed', 'Lanjutkan ke after-magang collaboration.', route('participant.collaboration')));

        return back()->with('status', 'Program ditandai completed.');
    }
}
