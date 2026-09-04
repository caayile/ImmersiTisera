<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Department;
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
        $q = request('q');
        $departments = Department::with(['businessUnits' => fn ($query) => $query->where('status', 'open')])
            ->withCount(['businessUnits' => fn ($query) => $query->where('status', 'open')])
            ->where('status', 'active')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('area', 'like', "%{$q}%")
                        ->orWhereHas('businessUnits', fn ($units) => $units->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderBy('area')
            ->orderBy('name')
            ->get();

        return view('public.departments', compact('departments', 'q'));
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
