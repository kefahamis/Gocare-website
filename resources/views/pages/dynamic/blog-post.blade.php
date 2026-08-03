@php
  $heroTitle = \Illuminate\Support\Str::before($record->title, '|');
  $metaTitle = $heroTitle . ' | ' . \App\Models\SeoSetting::current()->site_name;
  $bodyText = trim(collect($record->sections ?? [])->map(fn ($s) => strip_tags($s['body'] ?? ''))->implode(' '));
  $metaDescription = mb_substr($bodyText ?: strip_tags($record->excerpt ?? ''), 0, 160);
  $imageUrl = filled($record->image)
      ? (str_starts_with($record->image, 'http') || str_starts_with($record->image, '/') ? $record->image : '/'.ltrim($record->image, '/'))
      : '/images/news-bg.jpg';
  $imageFor = function (?string $image) {
      if (! filled($image)) {
          return null;
      }
      return str_starts_with($image, 'http') || str_starts_with($image, '/') ? $image : '/'.ltrim($image, '/');
  };
  $paragraphs = function (string $body): array {
      $parts = preg_split('/\s*\n+\s*/', trim($body));
      return array_values(array_filter($parts, fn ($p) => filled(trim($p))));
  };
  $blocks = collect($record->sections ?? [])
      ->filter(fn ($s) => filled($s['heading'] ?? null) || filled($s['body'] ?? null) || filled($s['link'] ?? null) || filled($s['image'] ?? null))
      ->values();
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .bp-wrap { max-width: 820px; margin: 0 auto; padding: 60px 32px 100px; }
    .bp-eyebrow { display:inline-flex; align-items:center; gap:7px; background:var(--ol); color:var(--o); padding:5px 16px; border-radius:50px; font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; margin-bottom:20px; }
    .bp-title { font-family:'Outfit',sans-serif; font-size:2.6rem; font-weight:900; color:var(--dark); line-height:1.2; margin-bottom:12px; }
    .bp-subtitle { font-size:1.1rem; color:#6B7280; margin-bottom:24px; }
    .bp-meta { display:flex; align-items:center; gap:16px; font-size:.82rem; color:#94a3b8; margin-bottom:36px; padding-bottom:28px; border-bottom:1px solid var(--border); flex-wrap:wrap; }
    .bp-meta span { display:flex; align-items:center; gap:5px; }
    .bp-hero { width:100%; height:420px; object-fit:cover; border-radius:16px; margin-bottom:44px; display:block; }
    .bp-body { font-size:1.02rem; color:#374151; line-height:1.82; }
    .bp-body h2 { font-family:'Outfit',sans-serif; font-size:1.55rem; font-weight:800; color:var(--dark); margin:44px 0 14px; }
    .bp-body h3 { font-size:1.15rem; font-weight:700; color:var(--dark); margin:28px 0 10px; }
    .bp-body p { margin-bottom:18px; }
    .bp-body ul, .bp-body ol { margin:0 0 20px; padding-left:0; list-style:none; }
    .bp-body ul li, .bp-body ol li { padding:5px 0 5px 28px; position:relative; }
    .bp-body ul li::before { content:''; position:absolute; left:0; top:13px; width:8px; height:8px; border-radius:50%; background:var(--o); }
    .bp-body ol { counter-reset:ol; }
    .bp-body ol li { counter-increment:ol; }
    .bp-body ol li::before { content:counter(ol)'.'; position:absolute; left:0; top:5px; font-weight:700; color:var(--o); }
    .bp-body strong { color:var(--dark); }
    .bp-body img { max-width:100%; height:auto; border-radius:16px; }
    .bp-callout { background:var(--ol); border-left:4px solid var(--o); border-radius:0 10px 10px 0; padding:20px 24px; margin:28px 0; }
    .bp-callout p { margin:0; font-weight:600; color:var(--dark); }
    .bp-cta { background:linear-gradient(135deg,var(--dark),#2d0f3c); border-radius:16px; padding:44px 40px; text-align:center; margin-top:56px; }
    .bp-cta h3 { font-family:'Outfit',sans-serif; font-size:1.6rem; color:#fff; margin-bottom:10px; }
    .bp-cta p { color:rgba(255,255,255,.75); margin-bottom:24px; }
    .bp-cta a { display:inline-flex; align-items:center; gap:8px; background:var(--o); color:#fff; font-weight:700; padding:13px 30px; border-radius:50px; transition:.2s; }
    .bp-cta a:hover { background:#d4611a; transform:translateY(-2px); }
    .bp-split { display:grid; grid-template-columns:1fr 1fr; gap:36px; align-items:center; margin:44px 0; }
    .bp-split--rev .bp-split-media { order:-1; }
    .bp-split-media img { width:100%; height:280px; object-fit:cover; border-radius:16px; }
    .bp-split h3 { font-family:'Outfit',sans-serif; font-size:1.4rem; font-weight:800; color:var(--dark); margin-bottom:10px; }
    .bp-back { display:inline-flex; align-items:center; gap:6px; color:var(--o); font-weight:700; font-size:.88rem; margin-bottom:32px; }
    .bp-back:hover { color:var(--dark); }
    @media(max-width:640px){ .bp-wrap{padding:32px 20px 80px;} .bp-title{font-size:1.8rem;} .bp-hero{height:240px;} .bp-cta{padding:32px 24px;} .bp-split{grid-template-columns:1fr; gap:20px;} .bp-split--rev .bp-split-media{order:0;} .bp-split-media img{height:200px;} }
  </style>

  <main>
    <div class="bp-wrap">
      <a href="/blog" class="bp-back"><i data-lucide="arrow-left"></i> Back to Blog</a>
      <span class="bp-eyebrow"><i data-lucide="edit-3"></i> {{ $record->category ?? 'GoCare Blog' }}</span>
      <h1 class="bp-title">{{ $heroTitle }}</h1>
      @if ($record->excerpt)
        <p class="bp-subtitle">{{ $record->excerpt }}</p>
      @endif
      <div class="bp-meta">
        <span><i data-lucide="clock"></i> GoCare Blog</span>
        @if ($record->published_at)<span><i data-lucide="calendar"></i> {{ $record->published_at->format('F j, Y') }}</span>@endif
        <span><i data-lucide="tag"></i> GoCare Training Institute</span>
      </div>
      <img loading="eager" decoding="async" class="bp-hero" src="{{ $imageUrl }}" alt="{{ $heroTitle }}">

      <div class="bp-body">
        @if (! $record->use_builder && filled($record->content))
          {!! $record->content !!}
        @else
          @forelse ($blocks as $block)
            @php
              $type = $block['type'] ?? 'content';
              $heading = $block['heading'] ?? null;
              $body = $block['body'] ?? null;
              $image = $imageFor($block['image'] ?? null);
              $link = $block['link'] ?? null;
              $linkLabel = $block['link_label'] ?? 'Learn More';
            @endphp
            @if ($type === 'callout')
              <div class="bp-callout"><p>{{ $body }}</p></div>
            @elseif ($type === 'cta')
              <div class="bp-cta">
                @if ($heading)<h3>{{ $heading }}</h3>@endif
                @if ($body)<p>{{ $body }}</p>@endif
                @if ($link)<a href="{{ str_starts_with($link, 'http') || str_starts_with($link, '/') ? $link : '/'.$link }}">{{ $linkLabel }} <i data-lucide="arrow-right"></i></a>@endif
              </div>
            @elseif ($type === 'split')
              <div class="bp-split{{ ($block['alignment'] ?? 'right') === 'left' ? ' bp-split--rev' : '' }}">
                <div>
                  @if ($heading)<h3>{{ $heading }}</h3>@endif
                  @foreach ($paragraphs($body ?? '') as $paragraph)
                    <p>{{ $paragraph }}</p>
                  @endforeach
                  @if ($link)<a href="{{ str_starts_with($link, 'http') || str_starts_with($link, '/') ? $link : '/'.$link }}" class="bp-cta-link" style="color:var(--o);font-weight:700;display:inline-flex;align-items:center;gap:6px;margin-top:8px;">{{ $linkLabel }} <i data-lucide="arrow-right"></i></a>@endif
                </div>
                @if ($image)
                  <div class="bp-split-media"><img loading="lazy" decoding="async" src="{{ $image }}" alt="{{ strip_tags($heading ?? '') }}"></div>
                @endif
              </div>
            @else
              @if ($heading)<h2>{{ $heading }}</h2>@endif
              @foreach ($paragraphs($body ?? '') as $paragraph)
                <p>{{ $paragraph }}</p>
              @endforeach
              @if ($link)<a href="{{ str_starts_with($link, 'http') || str_starts_with($link, '/') ? $link : '/'.$link }}" style="color:var(--o);font-weight:700;display:inline-flex;align-items:center;gap:6px;margin:8px 0 18px;">{{ $linkLabel }} <i data-lucide="arrow-right"></i></a>@endif
            @endif
          @empty
            <p>No content yet.</p>
          @endforelse
        @endif
      </div>

      <div class="bp-cta">
        <h3>Build Your Future with GoCare</h3>
        <p>Explore accredited programmes and start your journey with GoCare Training Institute.</p>
        <a href="/apply">Apply Now <i data-lucide="arrow-right"></i></a>
      </div>
    </div>
  </main>
@endsection
