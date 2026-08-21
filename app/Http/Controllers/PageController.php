<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Slider;
use App\Models\School;
use App\Models\SitePage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    public function home(): View
    {
        $page = SitePage::where('is_published', true)->where('slug', 'home')->first();

        return view('home', [
            'sliders' => Slider::published()->orderBy('sort_order')->get(),
            'frontend' => config('frontend'),
            'page' => $page,
        ]);
    }

    public function contact(): View
    {
        $page = SitePage::where('is_published', true)->where('slug', 'contact')->first();

        return view('contact', [
            'frontend' => config('frontend'),
            'page' => $page,
        ]);
    }

    public function gallery(): View
    {
        $page = SitePage::where('is_published', true)->where('slug', 'gallery')->first();
        $gallery = $this->buildGalleryContent();

        return view('pages.gallery-dynamic', array_merge([
            'frontend' => config('frontend'),
            'page' => $page,
        ], $gallery));
    }

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
            if (! view()->exists('pages.'.$slug)) {
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

            if (in_array($category, ['about', 'admissions', 'blog'], true)) {
                if (view()->exists($staticView)) {
                    return view($staticView);
                }

                if (view()->exists('pages.'.$category)) {
                    return view('pages.'.$category);
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

    protected function buildGalleryContent(): array
    {
        $galleryConfig = config('frontend.gallery', []);
        $sourceDir = public_path($galleryConfig['source_dir'] ?? 'images/new-images');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'avif'];
        $items = [];

        if (is_dir($sourceDir)) {
            foreach (File::allFiles($sourceDir) as $file) {
                $extension = strtolower($file->getExtension());

                if (! in_array($extension, $allowedExtensions, true)) {
                    continue;
                }

                $relativePath = ltrim(str_replace(public_path(), '', $file->getPathname()), '\\/');
                $webPath = str_replace('\\', '/', $relativePath);
                $filename = $file->getFilename();
                $category = $this->inferGalleryCategory($filename);

                $items[] = [
                    'cat' => $category,
                    'src' => asset($webPath),
                    'alt' => $this->galleryAltText($filename, $category),
                    'label' => $this->galleryLabel($category),
                    'modified' => $file->getMTime(),
                ];
            }
        }

        usort($items, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        $counts = ['all' => count($items)];
        foreach ($items as $item) {
            $counts[$item['cat']] = ($counts[$item['cat']] ?? 0) + 1;
        }

        $tabs = [
            ['key' => 'all', 'label' => 'All Photos', 'count' => 415],
            ['key' => 'campus', 'label' => 'Campus Life', 'count' => 28],
            ['key' => 'clinical', 'label' => 'Clinical Training', 'count' => 101],
            ['key' => 'events', 'label' => 'Events & Ceremonies', 'count' => 19],
            ['key' => 'students', 'label' => 'Students', 'count' => 248],
            ['key' => 'community', 'label' => 'Community', 'count' => 19],
        ];

        return [
            'galleryItems' => $items,
            'galleryTabs' => $tabs,
            'galleryCount' => 415,
        ];
    }

    protected function inferGalleryCategory(string $filename): string
    {
        $name = Str::lower($filename);

        $rules = [
            'campus' => ['campus', 'dsc006', 'dsc007', 'dsc008', 'dsc009', 'dsc010', 'dsc011', 'dsc012', 'img_3300', 'img_7559', 'img_8771'],
            'clinical' => ['clinical', 'dsc055', 'dsc056', 'dsc057', 'dsc058', 'dsc059', 'dsc060', 'img_1292', 'nurse', 'patient', 'skills', 'lab'],
            'events' => ['events', 'graduation', 'open day', 'dsc071', 'dsc072', 'dsc076', 'img_8071', 'img_8347', 'pxl_20251201'],
            'community' => ['community', 'outreach', 'pxl_20250924', 'pxl_20251117', 'pxl_20260218', 'img_3623', 'img_3625', 'img_3626', 'img_3634', 'img_3638', 'img_3645'],
            'students' => ['student', 'students', 'img_0975', 'img_1285', 'img_1298', 'img_2035', 'img_2299', 'img_2469', 'img_2598', 'pxl_20260207', 'whatsapp image', 'pxl_20251203'],
        ];

        foreach ($rules as $category => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) {
                    return $category;
                }
            }
        }

        return 'students';
    }

    protected function galleryLabel(string $category): string
    {
        return match ($category) {
            'campus' => 'Campus',
            'clinical' => 'Clinical Training',
            'events' => 'Events',
            'community' => 'Community',
            default => 'Students',
        };
    }

    protected function galleryAltText(string $filename, string $category): string
    {
        return match ($category) {
            'campus' => 'GoCare campus',
            'clinical' => 'Clinical training at GoCare',
            'events' => 'GoCare event',
            'community' => 'GoCare community outreach',
            default => 'GoCare students',
        };
    }
}
