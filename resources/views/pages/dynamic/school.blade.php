@php
  $heroTitle = \Illuminate\Support\Str::before($record->title, '|');
  $metaTitle = $heroTitle . ' | ' . \App\Models\SeoSetting::current()->site_name;
  $bodyText = trim(collect($record->sections ?? [])->map(fn ($s) => strip_tags($s['body'] ?? ''))->implode(' '));
  $metaDescription = mb_substr($bodyText ?: strip_tags($record->content ?? ''), 0, 160);
  $heroImage = filled($record->image)
      ? (str_starts_with($record->image, 'http') || str_starts_with($record->image, '/') ? $record->image : '/'.ltrim($record->image, '/'))
      : '/images/new-images/PALS.jpeg';
  $sections = collect($record->sections ?? [])->filter(fn ($s) => filled($s['heading'] ?? null) || filled($s['body'] ?? null))->values();
  $schoolIcon = str_contains(strtolower($heroTitle), 'hospital') ? 'utensils' : (str_contains(strtolower($heroTitle), 'social') ? 'users' : 'stethoscope');
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .sc-page { background:#fcf9f8; font-family:'Inter',sans-serif; color:#111; }
    .sc-page .ph { background:#0a0a0a; }
    .sc-ribbon { background:linear-gradient(270deg,var(--dark),var(--p),var(--o),var(--dark)); background-size:800% 800%; color:#fff; text-align:center; padding:25px; font-weight:800; font-size:1.5rem; font-style:italic; letter-spacing:2px; text-transform:uppercase; }
    .cc-section { padding:90px 0 110px; background:linear-gradient(180deg,#f8f4ff 0%,#fff8f3 50%,#fcf9f8 100%); position:relative; }
    .cc-section:before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,#4a1a6d,#ec7424,#4a1a6d); }
    .cc-header { text-align:center; margin-bottom:48px; }
    .cc-header h2 { font-family:'Outfit','Inter',sans-serif; font-size:2.6rem; font-weight:800; color:#2d1052; margin-bottom:12px; }
    .cc-header p { font-size:1.05rem; color:#64748b; max-width:560px; margin:0 auto; line-height:1.7; }
    .cc-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:28px; }
    .cc-card { background:#fff; border-radius:18px; border:1px solid rgba(0,0,0,.06); box-shadow:0 8px 30px rgba(0,0,0,.07); overflow:hidden; display:flex; flex-direction:column; transition:transform .3s,box-shadow .3s; text-decoration:none; color:inherit; }
    .cc-card:hover { transform:translateY(-10px); box-shadow:0 24px 52px rgba(74,26,109,.16); }
    .cc-card-top { height:190px; overflow:hidden; background:#e8e0f0; }
    .cc-card-top img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .5s; }
    .cc-card:hover .cc-card-top img { transform:scale(1.06); }
    .cc-card-body { padding:28px 28px 22px; flex:1; display:flex; flex-direction:column; gap:14px; }
    .cc-badge-row { display:flex; align-items:center; gap:10px; }
    .cc-badge { font-size:.72rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; padding:5px 14px; border-radius:50px; }
    .cc-badge.diploma { background:#f3e8ff; color:#6b21a8; }
    .cc-badge.certificate { background:#fff7ed; color:#c2410c; }
    .cc-badge.short-course { background:#f0fdfa; color:#0f766e; }
    .cc-title { font-family:'Outfit','Inter',sans-serif; font-size:1.1rem; font-weight:800; color:#1e1030; line-height:1.4; flex:1; }
    .cc-meta { font-size:.85rem; color:#64748b; line-height:1.6; white-space:pre-line; }
    .cc-card-footer { padding:0 22px 22px; display:flex; justify-content:flex-end; }
    .cc-cta { display:inline-flex; align-items:center; justify-content:center; width:46px; height:46px; border-radius:50%; background:var(--o); color:#fff; box-shadow:0 6px 20px rgba(236,116,36,.3); }
    .sc-legacy { max-width:880px; margin:0 auto; padding:80px 24px; font-size:1.02rem; line-height:1.8; color:#374151; }
    .sc-legacy h2,.sc-legacy h3 { color:var(--dark); font-family:'Outfit',sans-serif; }
    @media(max-width:960px){ .cc-grid{grid-template-columns:repeat(2,1fr);} }
    @media(max-width:580px){ .cc-grid{grid-template-columns:1fr;} .cc-header h2{font-size:2rem;} .sc-ribbon{font-size:1rem;} }
  </style>

  <div class="sc-page">
    <section class="ph">
      <div class="ph-bg"><img loading="eager" decoding="async" src="{{ $heroImage }}" alt="{{ $heroTitle }}"></div>
      <div class="ph-overlay"></div>
      <div class="ph-inner">
        <div class="ph-content">
          <span class="ph-eyebrow"><i data-lucide="{{ $schoolIcon }}"></i> {{ $heroTitle }}</span>
          <nav class="ph-breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a><i data-lucide="chevron-right"></i><a href="/courses">Schools &amp; Programs</a><i data-lucide="chevron-right"></i><span>{{ $heroTitle }}</span></nav>
          <h1>{{ $heroTitle }}</h1>
          @if ($bodyText)<p>{{ \Illuminate\Support\Str::limit($bodyText, 220) }}</p>@endif
          <div class="ph-btns"><a href="/apply" class="ph-btn-primary">Apply Now <i data-lucide="arrow-right"></i></a><a href="/courses" class="ph-btn-ghost">View All Courses <i data-lucide="arrow-right"></i></a></div>
        </div>
      </div>
    </section>

    <div class="sc-ribbon">Train with the Experts... Become an Expert!</div>

    @if ($sections->isNotEmpty())
      <section class="cc-section">
        <div class="wrap">
          <div class="cc-header"><h2>Our Programs</h2><p>Explore accredited programmes and build a career with {{ $heroTitle }}.</p></div>
          <div class="cc-grid">
            @foreach ($sections as $section)
              @php
                $sectionImage = filled($section['image'] ?? null) ? (str_starts_with($section['image'], 'http') || str_starts_with($section['image'], '/') ? $section['image'] : '/'.ltrim($section['image'], '/')) : $heroImage;
                $sectionType = str_contains(strtolower($section['heading'] ?? ''), 'diploma') ? 'diploma' : (str_contains(strtolower($section['heading'] ?? ''), 'short') ? 'short-course' : 'certificate');
              @endphp
              <a href="{{ filled($section['link'] ?? null) ? (str_starts_with($section['link'], '/') ? $section['link'] : '/'.ltrim($section['link'], '/')) : '/apply' }}" class="cc-card">
                <div class="cc-card-top"><img loading="lazy" decoding="async" src="{{ $sectionImage }}" alt="{{ $section['heading'] ?? $heroTitle }}"></div>
                <div class="cc-card-body"><div class="cc-badge-row"><span class="cc-badge {{ $sectionType }}">{{ ucwords(str_replace('-', ' ', $sectionType)) }}</span></div><div class="cc-title">{{ $section['heading'] ?? 'GoCare Programme' }}</div>@if ($section['body'])<div class="cc-meta">{{ $section['body'] }}</div>@endif</div>
                <div class="cc-card-footer"><span class="cc-cta"><i data-lucide="arrow-right"></i></span></div>
              </a>
            @endforeach
          </div>
        </div>
      </section>
    @elseif ($record->content)
      <div class="sc-legacy">{!! $record->content !!}</div>
    @endif
  </div>
@endsection
