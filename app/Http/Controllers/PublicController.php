<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Models\News;

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
                'Puspa Holistic Integrative Care',
                'Marketing',
                'Finance Accounting',
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
        $slugs = [
            'tspm',
            'tsic',
            'k33',
            'wjl',
            'assalam-hypermarket',
            'sd-al-firdaus',
            'puspa-holistic-integrative-care',
        ];

        $departments = Department::with(['businessUnits' => fn ($query) => $query->where('status', 'open')])
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        $departments = collect($slugs)
            ->map(fn ($slug) => $departments[$slug] ?? null)
            ->filter()
            ->values();

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

        return view('public.departments', compact('departments', 'hero', 'heroSlides'));
    }

    public function department(Department $department)
    {
        $department->load(['businessUnits' => fn ($q) => $q->where('status', 'open')]);
        $hero = HeroSetting::forPage('departments');

        return view('public.department', compact('department', 'hero'));
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
