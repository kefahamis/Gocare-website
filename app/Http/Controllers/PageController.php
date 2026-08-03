<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\School;
use App\Models\SitePage;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Slugs that must always render their static Blade view (forms, widgets, templates).
     *
     * @var array<int, string>
     */
    protected array $staticOnly = [
        'apply',
        'application-form',
        'application-form-step2',
        'application-form-step3',
        'application-form-step4',
        'application-form-step5',
        'application-form-step6',
        'chatbot',
        'course-details',
        'index',
    ];

    public function courses(): View
    {
        if (view()->exists('pages.courses')) {
            return view('pages.courses');
        }

        return view('pages.dynamic.courses-list', [
            'courses' => Course::where('is_published', true)
                ->orderBy('title')
                ->get(),
            'schools' => School::where('is_published', true)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function blog(): View
    {
        if (view()->exists('pages.blog')) {
            return view('pages.blog');
        }

        return view('pages.dynamic.blog-list', [
            'posts' => BlogPost::where('is_published', true)
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        if (in_array($slug, $this->staticOnly, true)) {
            abort_unless(view()->exists('pages.'.$slug), 404);

            return view('pages.'.$slug);
        }

        if ($page = SitePage::where('is_published', true)->where('slug', $slug)->first()) {
            if ($page->use_builder || ! view()->exists('pages.'.$slug)) {
                return view('pages.dynamic.page', ['record' => $page]);
            }

            return view('pages.'.$slug);
        }

        abort_unless(view()->exists('pages.'.$slug), 404);

        return view('pages.'.$slug);
    }

    public function category(string $slug, string $category): View
    {
        $record = match ($category) {
            'courses' => Course::where('is_published', true)->where('slug', $slug)->first(),
            'blog' => BlogPost::where('is_published', true)->where('slug', $slug)->first(),
            'schools' => School::where('is_published', true)->where('slug', $slug)->first(),
            'admissions', 'about' => SitePage::where('is_published', true)->where('slug', $slug)->first(),
            default => null,
        };

        if ($record) {
            $staticView = match ($category) {
                'blog' => 'pages.blog-'.$slug,
                default => 'pages.'.$category.'.'.$slug,
            };

            $builderEnabled = (bool) ($record->use_builder ?? false);

            if (in_array($category, ['about', 'admissions', 'blog'], true)) {
                if (! $builderEnabled) {
                    if (view()->exists($staticView)) {
                        return view($staticView);
                    }

                    if (view()->exists('pages.'.$category)) {
                        return view('pages.'.$category);
                    }
                }
            } elseif (view()->exists($staticView)) {
                return view($staticView);
            }

            $view = match ($category) {
                'courses' => 'pages.dynamic.course',
                'blog' => 'pages.dynamic.blog-post',
                'schools' => 'pages.dynamic.school',
                default => 'pages.dynamic.page',
            };

            return view($view, ['record' => $record]);
        }

        $view = $category === 'blog' ? 'pages.blog-'.$slug : 'pages.'.$category.'.'.$slug;

        abort_unless(view()->exists($view), 404);

        return view($view);
    }
}
