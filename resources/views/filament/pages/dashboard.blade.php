<x-filament-panels::page>
    <div class="gc-dash">
        {{-- Page title + breadcrumb --}}
        <div class="gc-dash-head">
            <div class="gc-dash-head-title">
                <h1 class="gc-dash-title">Dashboard</h1>
                <p class="gc-dash-sub">Welcome back, {{ $firstName }}</p>
            </div>
            <nav class="gc-dash-crumbs" aria-label="Breadcrumb">
                <a href="{{ \Filament\Facades\Filament::getHomeUrl() }}">Home</a>
                <span class="gc-dash-crumb-sep">/</span>
                <span aria-current="page">Dashboard</span>
            </nav>
        </div>

        {{-- Top row: welcome hero + stat cards --}}
        <div class="gc-dash-grid gc-dash-grid-top">
            <div class="gc-card gc-hero">
                <div class="gc-hero-inner">
                    <span class="gc-hero-eyebrow">GoCare Admin</span>
                    <h2 class="gc-hero-title">Operations at a glance</h2>
                    <p class="gc-hero-text">Monitor applications, content, and site activity from a single polished workspace.</p>
                    <a href="{{ \App\Filament\Pages\MediaLibrary::getUrl() }}" class="gc-hero-cta">
                        Open Media Library
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" width="16" height="16">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                <svg class="gc-hero-waves" viewBox="0 0 520 320" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 220 C120 120 200 260 320 170 S480 120 520 170 L520 320 L0 320 Z" fill="rgba(255,255,255,.10)" />
                    <path d="M0 260 C140 180 240 320 380 240 S500 210 520 250 L520 320 L0 320 Z" fill="rgba(255,255,255,.08)" />
                    <circle cx="420" cy="80" r="90" fill="rgba(255,255,255,.06)" />
                    <circle cx="70" cy="60" r="40" fill="rgba(255,255,255,.05)" />
                </svg>
            </div>

            @php
                $statCards = [
                    ['key' => 'applications', 'label' => 'Applications', 'icon' => 'doc', 'tone' => 'indigo'],
                    ['key' => 'courses', 'label' => 'Courses', 'icon' => 'book', 'tone' => 'violet'],
                    ['key' => 'blog_posts', 'label' => 'Blog Posts', 'icon' => 'pen', 'tone' => 'sky'],
                    ['key' => 'subscribers', 'label' => 'Newsletter', 'icon' => 'mail', 'tone' => 'teal'],
                ];
                $icons = [
                    'doc' => 'M5 3.75A1.75 1.75 0 0 1 6.75 2h6.5c.966 0 1.75.784 1.75 1.75v11.5A1.75 1.75 0 0 1 13.25 17h-6.5A1.75 1.75 0 0 1 5 15.25V3.75ZM7 5.25a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5H7Zm0 3a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5H7Z',
                    'book' => 'M6 3.5A1.5 1.5 0 0 1 7.5 2h7A1.5 1.5 0 0 1 16 3.5v13a1.5 1.5 0 0 1-1.5 1.5h-7A1.5 1.5 0 0 1 6 16.5v-13Zm2 .5v11h6V4H8Z M4 6.25a.75.75 0 0 1 .75-.75H5v13.5h-.25A.75.75 0 0 1 4 18.25V6.25Z',
                    'pen' => 'M15.586 2.586a2.1 2.1 0 0 1 2.971 2.971l-11.2 11.2a1 1 0 0 1-.443.26l-3.55.888a1 1 0 0 1-1.203-1.204l.888-3.55a1 1 0 0 1 .26-.442l11.277-11.123ZM13.586 4 4.5 13.086l-.71 2.124 2.125-.71L15 5.414 13.586 4Z',
                    'mail' => 'M2 5.25A2.25 2.25 0 0 1 4.25 3h11.5A2.25 2.25 0 0 1 18 5.25v9.5A2.25 2.25 0 0 1 15.75 17H4.25A2.25 2.25 0 0 1 2 14.75v-9.5ZM4 5.41v.17l5.46 4.24a1.75 1.75 0 0 0 2.08 0L17 5.58v-.33a.75.75 0 0 0-.75-.75H4.75a.75.75 0 0 0-.75.75Zm13 .89-4.36 3.39 4.36 3.72V6.3Zm-1.75 8.37-4.31-3.68-1.02.8a1.75 1.75 0 0 1-2.08 0l-1.02-.8L4.75 14.67v.08c0 .414.336.75.75.75h9a.75.75 0 0 0 .75-.75v-.08Z',
                ];
            @endphp

            @foreach ($statCards as $card)
                @php $value = $stats[$card['key']] ?? 0; @endphp
                <a class="gc-card gc-stat gc-stat--{{ $card['tone'] }}" href="#">
                    <span class="gc-stat-icon">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" width="22" height="22">
                            <path fill-rule="evenodd" d="{{ $icons[$card['icon']] }}" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span class="gc-stat-meta">
                        <span class="gc-stat-label">{{ $card['label'] }}</span>
                        <span class="gc-stat-value">{{ number_format($value) }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Bottom row: recent applications + growth + filament card --}}
        <div class="gc-dash-grid gc-dash-grid-bottom">
            <div class="gc-card gc-orders">
                <div class="gc-card-head">
                    <div>
                        <h3 class="gc-card-title">Recent Applications</h3>
                        <p class="gc-card-sub">Latest submissions from the application form</p>
                    </div>
                    <div class="gc-card-actions">
                        <div class="gc-search">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" width="15" height="15">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 19-3.75-3.75M8.75 15a6.25 6.25 0 1 0 0-12.5 6.25 6.25 0 0 0 0 12.5Z" />
                            </svg>
                            <input type="search" placeholder="Search applications..." aria-label="Search applications" />
                        </div>
                        <button class="gc-more" type="button" aria-label="More options">
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" width="18" height="18">
                                <path d="M10 4.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5ZM10 8.25a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5ZM10 12a1.75 1.75 0 1 1 0 3.5A1.75 1.75 0 0 1 10 12Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                @if ($recentApplications->isEmpty())
                    <div class="gc-empty">No applications yet. They will appear here once submitted.</div>
                @else
                    <div class="gc-table-wrap">
                        <table class="gc-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="gc-th-select">
                                        <span class="gc-check" aria-hidden="true"></span>
                                    </th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">ID</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $statusMap = [
                                        'submitted' => ['Submitted', 'is-info'],
                                        'reviewing' => ['Reviewing', 'is-warning'],
                                        'accepted' => ['Accepted', 'is-success'],
                                        'rejected' => ['Rejected', 'is-danger'],
                                    ];
                                @endphp
                                @foreach ($recentApplications as $application)
                                    @php
                                        $status = $statusMap[$application->status] ?? [ucfirst($application->status), 'is-neutral'];
                                    @endphp
                                    <tr>
                                        <td class="gc-th-select"><span class="gc-check" aria-hidden="true"></span></td>
                                        <td>
                                            <div class="gc-customer">
                                                <span class="gc-avatar-sm">{{ $initials }}</span>
                                                <span class="gc-customer-name">{{ $application->data['application'] ?? 'Application' }}</span>
                                            </div>
                                        </td>
                                        <td><span class="gc-id">{{ $application->reference }}</span></td>
                                        <td><span class="gc-badge {{ $status[1] }}">{{ $status[0] }}</span></td>
                                        <td class="gc-date">{{ optional($application->submitted_at)->format('M j, Y') ?? $application->created_at->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="gc-dash-col">
                <div class="gc-card gc-growth">
                    <div class="gc-card-head">
                        <div>
                            <h3 class="gc-card-title">Our Growth</h3>
                            <p class="gc-card-sub">Applications per month</p>
                        </div>
                        <button class="gc-more" type="button" aria-label="More options">
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" width="18" height="18">
                                <path d="M10 4.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5ZM10 8.25a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5ZM10 12a1.75 1.75 0 1 1 0 3.5A1.75 1.75 0 0 1 10 12Z" />
                            </svg>
                        </button>
                    </div>
                    @php $maxValue = max($applicationsByMonth) ?: 1; @endphp
                    <div class="gc-chart" role="img" aria-label="Bar chart of applications for the last 12 months">
                        @foreach ($applicationsByMonth as $i => $count)
                            <div class="gc-chart-col">
                                <span class="gc-chart-tip">{{ $count }}</span>
                                <span class="gc-chart-bar" style="height: {{ max(4, round($count / $maxValue * 100)) }}%"></span>
                                <span class="gc-chart-label">{{ $chartLabels[$i] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="gc-card gc-brand">
                    <div class="gc-brand-body">
                        <span class="gc-brand-logo">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" width="26" height="26">
                                <path d="M13 2 3 14h7v8l10-12h-7V2Z" fill="currentColor" />
                            </svg>
                        </span>
                        <strong class="gc-brand-name">filament</strong>
                        <span class="gc-brand-version">{{ $filamentVersion }}</span>
                    </div>
                    <div class="gc-brand-links">
                        <a href="https://filamentphp.com/docs" target="_blank" rel="noopener noreferrer">Documentation</a>
                        <a href="https://github.com/filamentphp/filament" target="_blank" rel="noopener noreferrer">GitHub</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
