<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Models\News;
use App\Support\DepartmentFilters;
use App\Support\StudyPrograms;

class PublicController extends Controller
{
    public function home()
    {
        $departments = Department::withCount(['businessUnits' => fn ($q) => $q->where('status', 'open')])
            ->where('status', 'active')
            ->get();

        $featuredUnits = BusinessUnit::with(['department.businessUnits'])
            ->where('status', 'open')
            ->whereIn('name', [
                'Operation (Sales)',
                'Production',
                'Store Operation',
                'SD Al-Firdaus',
                'Puspa Holistic',
                'Marketing',
                'Finance Accounting & IT',
                'Digital Business',
                'IT',
                'Center Of Excellence',
            ])
            ->get()
            ->unique('name')
            ->take(3)
            ->values();

        if ($featuredUnits->count() < 3) {
            $featuredUnits = BusinessUnit::with(['department.businessUnits'])
                ->where('status', 'open')
                ->latest()
                ->take(3)
                ->get();
        }

        $latestNews = News::published()->latest('published_at')->take(3)->get();

        return view('public.home', compact('departments', 'featuredUnits', 'latestNews'));
    }

    public function departments()
    {
        $filters = [
            'q' => request('q'),
            'sort' => request('sort', 'relevan'),
            'area' => request('area', []),
            'field' => request('field', []),
            'placement' => request('placement', []),
            'prodi' => request('prodi', []),
        ];

        $selected = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'sort' => (string) ($filters['sort'] ?: 'relevan'),
            'area' => DepartmentFilters::normalizeList($filters['area']),
            'field' => DepartmentFilters::normalizeList($filters['field']),
            'placement' => DepartmentFilters::normalizeList($filters['placement']),
            'prodi' => DepartmentFilters::normalizeList($filters['prodi']),
        ];

        $query = Department::with(['businessUnits' => fn ($query) => $query->where('status', 'open')])
            ->withCount(['businessUnits' => fn ($query) => $query->where('status', 'open')])
            ->where('status', 'active');

        DepartmentFilters::apply($query, $selected);

        $departments = DepartmentFilters::filterPlacement($query->get(), $selected['placement']);

        $hero = HeroSetting::forPage('departments');
        $heroSlides = HeroSlide::forPage('departments')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HeroSlide $slide) => [
                'id' => $slide->id,
                'title' => $slide->title,
                'subtitle' => $slide->subtitle,
                'image' => $slide->imageUrl(),
                'url' => $slide->link_url ?: route('departments.index'),
            ])
            ->values();

        return view('public.departments', [
            'departments' => $departments,
            'q' => $selected['q'],
            'selected' => $selected,
            'filterAreas' => DepartmentFilters::availableAreas(),
            'filterFields' => array_keys(DepartmentFilters::fields()),
            'filterPlacements' => DepartmentFilters::placementTypes(),
            'filterSorts' => DepartmentFilters::sortOptions(),
            'filterProdis' => StudyPrograms::allPrograms(),
            'hero' => $hero,
            'heroSlides' => $heroSlides,
        ]);
    }

    public function department(Department $department)
    {
        $department->load(['businessUnits' => fn ($q) => $q->where('status', 'open')]);

        return view('public.department', compact('department'));
    }

    public function unit(BusinessUnit $businessUnit)
    {
        $businessUnit->load(['department', 'mentors.user']);

        return view('public.unit', compact('businessUnit'));
    }

    public function news()
    {
        $items = News::published()->latest('published_at')->paginate(9);

        return view('public.news', compact('items'));
    }

    public function newsShow(News $news)
    {
        abort_unless($news->status === 'published' && $news->published_at && $news->published_at <= now(), 404);

        return view('public.news-show', compact('news'));
    }

    public function profile()
    {
        $user = auth()->user()->load(['participant', 'mentor.department', 'mentor.businessUnit']);
        $program = null;

        if ($user->participant) {
            $program = $user->participant->programs()
                ->with(['department', 'businessUnit', 'mentor.user'])
                ->latest()
                ->first();
        }

        return view('public.profile', compact('user', 'program'));
    }

    public function programInfo()
    {
        return view('public.program-info');
    }
}
