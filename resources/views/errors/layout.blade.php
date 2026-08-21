@php
  $code = $code ?? '404';
  $title = $title ?? 'Page not found';
  $eyebrow = $eyebrow ?? 'GoCare Training Institute';
  $icon = $icon ?? 'compass';
  $message = $message ?? 'The page you are looking for has moved, been renamed, or never existed.';
  $primaryLabel = $primaryLabel ?? 'Back to Home';
  $primaryUrl = $primaryUrl ?? '/';
  $secondaryLabel = $secondaryLabel ?? 'Talk to Us';
  $secondaryUrl = $secondaryUrl ?? '/contact';
  $showLinks = $showLinks ?? true;
  $metaTitle = $metaTitle ?? $code.' '.$title.' | '.\App\Models\SeoSetting::current()->site_name;
  $metaDescription = $metaDescription ?? $message;

  $quickLinks = $quickLinks ?? [
      ['icon' => 'graduation-cap', 'label' => 'Our Courses', 'note' => 'Healthcare, hospitality, business & ICT', 'url' => '/courses'],
      ['icon' => 'file-text', 'label' => 'Admissions', 'note' => 'Entry requirements, fees & intakes', 'url' => '/admissions'],
      ['icon' => 'send', 'label' => 'Apply Online', 'note' => 'Start your application in minutes', 'url' => '/apply'],
      ['icon' => 'newspaper', 'label' => 'Blog & Guides', 'note' => 'Career advice and course guides', 'url' => '/blog'],
      ['icon' => 'info', 'label' => 'About GoCare', 'note' => 'Our story, accreditation & campus', 'url' => '/about'],
      ['icon' => 'phone', 'label' => 'Contact Us', 'note' => 'Call, email or visit the institute', 'url' => '/contact'],
  ];
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .gc-err {
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #4a1d6d 0%, #642a7e 55%, #7d3a9c 100%);
      color: #F5F0E8;
      padding: 132px 0 96px;
    }
    .gc-err::before,
    .gc-err::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
    }
    .gc-err::before {
      width: 520px; height: 520px;
      top: -220px; right: -140px;
      background: radial-gradient(circle, rgba(236,116,36,.35), transparent 68%);
    }
    .gc-err::after {
      width: 420px; height: 420px;
      bottom: -200px; left: -120px;
      background: radial-gradient(circle, rgba(255,255,255,.14), transparent 70%);
    }
    .gc-err-inner { position: relative; z-index: 2; text-align: center; }
    .gc-err-eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.28);
      border-radius: 50px;
      padding: 7px 18px;
      font-size: .78rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
    }
    .gc-err-eyebrow svg, .gc-err-eyebrow .lucide { width: 15px; height: 15px; color: #ec7424; }
    .gc-err-code {
      font-family: 'Outfit', 'Inter', sans-serif;
      font-size: clamp(6rem, 20vw, 12rem);
      font-weight: 700;
      line-height: .9;
      margin: 22px 0 6px;
      letter-spacing: -.03em;
      background: linear-gradient(180deg, #ffffff 12%, #ec7424 108%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    .gc-err h1 {
      font-size: clamp(1.6rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 16px;
      color: #F5F0E8;
    }
    .gc-err-msg {
      max-width: 620px;
      margin: 0 auto;
      font-size: 1.05rem;
      line-height: 1.8;
      color: rgba(245,240,232,.82);
    }
    .gc-err-btns {
      display: flex; flex-wrap: wrap;
      justify-content: center; gap: 14px;
      margin-top: 34px;
    }
    .gc-err-links {
      position: relative;
      z-index: 2;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-top: 64px;
      text-align: left;
    }
    .gc-err-link {
      display: flex; align-items: flex-start; gap: 14px;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.16);
      border-radius: 18px;
      padding: 20px 22px;
      text-decoration: none;
      color: #F5F0E8;
      transition: all .25s cubic-bezier(.25,.46,.45,.94);
    }
    .gc-err-link:hover {
      background: rgba(255,255,255,.16);
      border-color: rgba(236,116,36,.65);
      transform: translateY(-3px);
    }
    .gc-err-link-icon {
      width: 40px; height: 40px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      background: rgba(236,116,36,.18);
      border: 1px solid rgba(236,116,36,.4);
      border-radius: 12px;
    }
    .gc-err-link-icon svg, .gc-err-link-icon .lucide { width: 18px; height: 18px; color: #ec7424; }
    .gc-err-link strong { display: block; font-size: .98rem; font-weight: 700; margin-bottom: 3px; }
    .gc-err-link small { font-size: .82rem; line-height: 1.5; color: rgba(245,240,232,.72); }
    .gc-err-help {
      position: relative; z-index: 2;
      margin-top: 40px; text-align: center;
      font-size: .9rem; color: rgba(245,240,232,.7);
    }
    .gc-err-help a { color: #ec7424; font-weight: 700; text-decoration: none; }
    .gc-err-help a:hover { text-decoration: underline; }
    @media (max-width: 900px) {
      .gc-err { padding: 108px 0 72px; }
      .gc-err-links { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
      .gc-err-links { grid-template-columns: 1fr; margin-top: 44px; }
    }
  </style>

  <section class="gc-err">
    <div class="wrap gc-err-inner">
      <span class="gc-err-eyebrow"><i data-lucide="{{ $icon }}"></i> {{ $eyebrow }}</span>
      <div class="gc-err-code">{{ $code }}</div>
      <h1>{{ $title }}</h1>
      <p class="gc-err-msg">{{ $message }}</p>
      <div class="gc-err-btns">
        <a href="{{ $primaryUrl }}" class="ph-btn-primary">{{ $primaryLabel }} <i data-lucide="arrow-right"></i></a>
        <a href="{{ $secondaryUrl }}" class="ph-btn-ghost">{{ $secondaryLabel }} <i data-lucide="message-circle"></i></a>
      </div>
    </div>

    @if ($showLinks)
      <div class="wrap gc-err-links">
        @foreach ($quickLinks as $link)
          <a href="{{ $link['url'] }}" class="gc-err-link">
            <span class="gc-err-link-icon"><i data-lucide="{{ $link['icon'] }}"></i></span>
            <span>
              <strong>{{ $link['label'] }}</strong>
              <small>{{ $link['note'] }}</small>
            </span>
          </a>
        @endforeach
      </div>
    @endif

    <div class="wrap gc-err-help">
      Still stuck? Call <a href="tel:+254703115502">+254 703 115 502</a>
      or email <a href="mailto:info@gocareinstitute.ac.ke">info@gocareinstitute.ac.ke</a>.
    </div>
  </section>
@endsection
