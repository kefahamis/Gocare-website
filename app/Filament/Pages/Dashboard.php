<?php

namespace App\Filament\Pages;

use App\Models\Application;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\NewsletterSubscriber;
use App\Models\School;
use Composer\InstalledVersions;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $userName = filled($user?->name) ? $user->name : 'GoCare Administrator';
        $firstName = Str::of($userName)->before(' ')->toString() ?: $userName;
        $initials = Str::upper(Str::substr($userName, 0, 1))
            .Str::upper(Str::substr(Str::afterLast($userName, ' '), 0, 1));

        $stats = [
            'applications' => Application::count(),
            'courses' => Course::count(),
            'blog_posts' => BlogPost::count(),
            'schools' => School::count(),
            'subscribers' => NewsletterSubscriber::count(),
            'messages' => ContactMessage::count(),
        ];

        $recentApplications = Application::query()
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        [$chartLabels, $applicationsByMonth, $messagesByMonth] = $this->buildMonthlySeries();

        return [
            'user' => $user,
            'userName' => $userName,
            'firstName' => $firstName,
            'initials' => $initials,
            'stats' => $stats,
            'recentApplications' => $recentApplications,
            'chartLabels' => $chartLabels,
            'applicationsByMonth' => $applicationsByMonth,
            'messagesByMonth' => $messagesByMonth,
            'filamentVersion' => InstalledVersions::getPrettyVersion('filament/filament') ?? '4.x',
        ];
    }

    /**
     * @return array{0: list<string>, 1: list<int>, 2: list<int>}
     */
    protected function buildMonthlySeries(): array
    {
        $labels = [];
        $applications = [];
        $messages = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $labels[] = $month->format('M');

            $applications[] = Application::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $messages[] = ContactMessage::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [$labels, $applications, $messages];
    }
}
