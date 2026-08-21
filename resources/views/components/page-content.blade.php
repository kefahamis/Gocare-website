@php
  $heroTitle = $heroTitle ?? null;
  $heroAccent = $heroAccent ?? null;
  $heroKicker = $heroKicker ?? 'GoCare Training Institute';
  $heroIntro = $heroIntro ?? null;
  $heroImage = $heroImage ?? null;
  $heroFallbackImage = $heroFallbackImage ?? '/images/new-images/courses/hero-courses.jpeg';
  $heroCtaLabel = $heroCtaLabel ?? 'Apply Now';
  $heroCtaUrl = $heroCtaUrl ?? '/apply';
  $heroParentLabel = $heroParentLabel ?? null;
  $heroParentUrl = $heroParentUrl ?? null;
  $linkLabel = $linkLabel ?? 'Learn More';
  $legacyContent = $legacyContent ?? null;

  $imageUrl = function (?string $img): ?string {
      if (! filled($img)) {
          return null;
      }
      return str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : '/'.ltrim($img, '/');
  };

  $paragraphs = function (string $body): array {
      $parts = preg_split('/\s*\n+\s*/', trim($body));
      return array_values(array_filter($parts, fn ($p) => filled(trim($p))));
  };

  $sections = collect($sections ?? [])
      ->filter(fn ($s) => filled($s['heading'] ?? null) || filled($s['body'] ?? null) || filled($s['link'] ?? null))
      ->values();
@endphp

<style>
  .gc-edit .ph { background: #0a0a0a; }
  .gc-edit .gc-sec { padding: 88px 0; }
  .gc-edit .gc-sec--alt { background: #f5ebd2; }
  .gc-edit .gc-sec-block { display: grid; grid-template-columns: 1.05fr .95fr; gap: 64px; align-items: center; }
  .gc-edit .gc-sec-block--rev .gc-sec-media { order: -1; }
  .gc-edit .gc-sec-block--text { display: block; max-width: 880px; }
  .gc-edit .gc-sec-copy p { font-size: 1.05rem; line-height: 1.8; color: var(--gray); margin-bottom: 18px; }
  .gc-edit .gc-sec-copy p:last-child { margin-bottom: 0; }
  .gc-edit .gc-sec-btn { margin-top: 26px; }
  .gc-edit .gc-sec--callout { background: linear-gradient(135deg, #4a1d6d, #6c2d8c); color: #fff; }
  .gc-edit .gc-sec--callout .course-sec-title, .gc-edit .gc-sec--callout .gc-sec-copy p { color: #fff; }
  .gc-edit .gc-sec--cta { background: #f5ebd2; text-align: center; }
  .gc-edit .gc-sec--cta .gc-sec-block--text { max-width: 760px; }
  .gc-edit .gc-sec-media img {
    width: 100%; border-radius: 24px;
    box-shadow: 0 20px 44px rgba(74,26,109,.16);
    border: 6px solid #F5F0E8;
  }
  .gc-edit .gc-legacy { max-width: 880px; margin: 0 auto; }
  .gc-edit .gc-legacy p { font-size: 1.05rem; line-height: 1.8; color: var(--gray); margin-bottom: 18px; }
  .gc-edit .gc-legacy h1, .gc-edit .gc-legacy h2, .gc-edit .gc-legacy h3, .gc-edit .gc-legacy h4 {
    font-family: 'Outfit', 'Inter', sans-serif; color: var(--dark);
  }
  .gc-edit .gc-legacy img { max-width: 100%; height: auto; border-radius: 20px; }
  .gc-edit .gc-legacy ul, .gc-edit .gc-legacy ol { padding-left: 22px; margin-bottom: 18px; color: var(--gray); line-height: 1.8; }
  .gc-edit .gc-legacy a { color: var(--o); }
  @media (max-width: 900px) {
    .gc-edit .gc-sec-block { grid-template-columns: 1fr; gap: 32px; }
    .gc-edit .gc-sec-block--rev .gc-sec-media { order: 0; }
  }
</style>

<div class="gc-edit">

  @if ($heroTitle)
    <section class="ph">
      <div class="ph-bg">
        <img loading="eager" decoding="async" src="{{ $imageUrl($heroImage) ?? $heroFallbackImage }}" alt="{{ strip_tags($heroTitle) }}">
      </div>
      <div class="ph-overlay"></div>
      <div class="ph-inner">
        <div class="ph-content">
          <span class="ph-eyebrow"><i data-lucide="sparkles"></i> {{ $heroKicker }}</span>
          <nav class="ph-breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a><i data-lucide="chevron-right"></i>
            @if ($heroParentLabel)
              <a href="{{ $heroParentUrl }}">{{ $heroParentLabel }}</a><i data-lucide="chevron-right"></i>
            @endif
            <span>{{ $heroTitle }}</span>
          </nav>
          <h1>{{ $heroTitle }}@if ($heroAccent)<em>{{ $heroAccent }}</em>@endif</h1>
          @if ($heroIntro)
            <p>{{ $heroIntro }}</p>
          @endif
          <div class="ph-btns">
            <a href="{{ $heroCtaUrl }}" class="ph-btn-primary">{{ $heroCtaLabel }} <i data-lucide="arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>
  @endif

  @forelse ($sections as $index => $section)
    @php
      $heading = $section['heading'] ?? null;
      $body = $section['body'] ?? null;
      $type = $section['type'] ?? 'content';
      $img = $imageUrl($section['image'] ?? null);
      $link = $section['link'] ?? null;
      $sectionLinkLabel = $section['link_label'] ?? $linkLabel;
    @endphp
    @if (! filled($heading) && ! filled($body) && ! filled($link) && ! $img)
      @continue
    @endif
    <section class="gc-sec gc-sec--{{ $type }}{{ $index % 2 === 1 ? ' gc-sec--alt' : '' }}">
      <div class="wrap gc-sec-block{{ $img ? (($section['alignment'] ?? ($index % 2 === 1 ? 'left' : 'right')) === 'left' ? ' gc-sec-block--rev' : '') : ' gc-sec-block--text' }}">
        <div class="gc-sec-copy">
          @if ($heading)
            <h2 class="course-sec-title">{{ $heading }}</h2>
          @endif
          @if ($body)
            @foreach ($paragraphs($body) as $paragraph)
              <p>{{ $paragraph }}</p>
            @endforeach
          @endif
          @if ($link)
            <a href="{{ str_starts_with($link, 'http') || str_starts_with($link, '/') ? $link : '/'.$link }}" class="ph-btn-primary gc-sec-btn">
               {{ $sectionLinkLabel }} <i data-lucide="arrow-right"></i>
            </a>
          @endif
        </div>
        @if ($img)
          <div class="gc-sec-media">
            <img loading="lazy" decoding="async" src="{{ $img }}" alt="{{ strip_tags($heading ?? '') }}">
          </div>
        @endif
      </div>
    </section>
  @empty
    @if ($legacyContent)
      <section class="gc-sec">
        <div class="wrap">
          <div class="gc-legacy">{!! $legacyContent !!}</div>
        </div>
      </section>
    @endif
  @endforelse

</div>
