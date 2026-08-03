@php
  $heroTitle = \Illuminate\Support\Str::before($record->title, '|');
  $metaTitle = $heroTitle . ' | ' . \App\Models\SeoSetting::current()->site_name;
  $bodyText = trim(collect($record->sections ?? [])->map(fn ($s) => strip_tags($s['body'] ?? ''))->implode(' '));
  $metaDescription = mb_substr($bodyText ?: strip_tags($record->summary ?? ''), 0, 160);
  $heroImage = filled($record->image)
      ? (str_starts_with($record->image, 'http') || str_starts_with($record->image, '/') ? $record->image : '/'.ltrim($record->image, '/'))
      : '/images/new-images/courses/hero-courses.jpeg';
  $titleParts = preg_split('/\s*\/\s*/', $heroTitle, 2);
  $sections = collect($record->sections ?? [])->filter(fn ($s) => filled($s['heading'] ?? null) || filled($s['body'] ?? null))->values();
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .course-hero { background:#0a0a0a; }
    .course-hero-content { max-width:1200px; }
    .course-hero .ph-breadcrumb { margin-bottom:18px; }
    .course-hero .hero-text h1 { max-width:760px; }
    .course-main { background:#f5ebd2; }
    .course-description p, .module-info p { white-space:pre-line; }
    .course-description img { max-width:100%; height:auto; border-radius:20px; margin-top:20px; }
    .course-body .course-sec-title { margin-top:0; }
    .course-body .modules-list { padding-top:0 !important; }
    @media(max-width:640px){ .course-main{padding:48px 0;} .course-grid-layout{padding:0 20px;} .requirements-grid{grid-template-columns:1fr;} }
  </style>

  <header class="course-hero">
    <div class="hero-visual">
      <img loading="eager" decoding="async" src="{{ $heroImage }}" alt="{{ $heroTitle }}">
    </div>
    <div class="wrap">
      <div class="course-hero-content">
        <div class="hero-text">
          <span class="course-badge"><i data-lucide="award"></i> {{ $record->school ?: 'Accredited Programme' }}</span>
          <nav class="ph-breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a><i data-lucide="chevron-right"></i><a href="/courses">Courses</a><i data-lucide="chevron-right"></i><span>{{ $heroTitle }}</span>
          </nav>
          <h1>{{ $titleParts[0] }}@if (isset($titleParts[1])) <span style="color:var(--o)">{{ $titleParts[1] }}</span>@endif</h1>
          @if ($record->summary)<p class="intro">{{ $record->summary }}</p>@endif

          <div class="course-meta-grid">
            <div class="course-meta-item"><i data-lucide="graduation-cap"></i><strong>Accredited</strong><span>Programme</span></div>
            <div class="course-meta-item"><i data-lucide="book-open"></i><strong>{{ $record->school ?: 'GoCare' }}</strong><span>School</span></div>
            <div class="course-meta-item"><i data-lucide="calendar"></i><strong>Open</strong><span>Intakes</span></div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="course-main">
    <div class="wrap">
      <div class="course-grid-layout">
        <div class="course-body">
          @if ($sections->isNotEmpty())
            @php $overview = $sections->first(); @endphp
            <section class="course-description">
              <h2 class="course-sec-title">{{ $overview['heading'] ?? 'Programme Overview' }}</h2>
              @foreach (preg_split('/\s*\n+\s*/', trim($overview['body'] ?? '')) as $paragraph)
                @if (filled(trim($paragraph)))<p>{{ $paragraph }}</p>@endif
              @endforeach
              @if (!empty($overview['image']))
                <img loading="lazy" decoding="async" src="{{ str_starts_with($overview['image'], '/') ? $overview['image'] : '/'.ltrim($overview['image'], '/') }}" alt="{{ $overview['heading'] ?? $heroTitle }}">
              @endif
            </section>

            @if ($sections->count() > 1)
              <section class="modules-list">
                <h2 class="course-sec-title">What You'll Learn</h2>
                @foreach ($sections->skip(1) as $index => $section)
                  <div class="module-item">
                    <div class="module-num">{{ str_pad($index + 2, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="module-info">
                      <h4>{{ $section['heading'] ?? 'Programme Module' }}</h4>
                      @if (!empty($section['body']))<p>{{ $section['body'] }}</p>@endif
                    </div>
                  </div>
                @endforeach
              </section>
            @endif
          @elseif ($record->content)
            <section class="course-description">
              <h2 class="course-sec-title">Programme Overview</h2>
              {!! $record->content !!}
            </section>
          @endif
        </div>

        <aside class="course-sidebar">
          <div class="sidebar-card">
            <div class="price-box">
              <span class="price-label">Ready to enrol?</span>
              <span class="price-value">Apply Now</span>
              <span class="price-sub">Admissions are open</span>
            </div>
            <div class="sidebar-btns">
              <a href="/apply" class="btn btn-primary">Start Your Application <i data-lucide="arrow-right"></i></a>
              <a href="/contact" class="btn btn-outline">Ask a Question</a>
            </div>
            <div class="sidebar-info-list">
              <div class="side-info-item"><span>School</span><strong>{{ $record->school ?: 'GoCare' }}</strong></div>
              <div class="side-info-item"><span>Accreditation</span><strong>Recognised</strong></div>
              <div class="side-info-item"><span>Intakes</span><strong>Multiple</strong></div>
            </div>
          </div>
          <div class="help-card">
            <h4>Need help choosing?</h4>
            <p>Our admissions team can help you find the right programme.</p>
            <a href="/contact" class="help-link">Contact Admissions <i data-lucide="arrow-right"></i></a>
          </div>
        </aside>
      </div>
    </div>
  </main>
@endsection
