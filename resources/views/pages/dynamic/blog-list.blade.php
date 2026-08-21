@php
  $metaTitle = 'Blog & Articles | ' . \App\Models\SeoSetting::current()->site_name;
  $metaDescription = 'Insights, tips and news from GoCare Training Institute on healthcare, hospitality and caregiving careers.';
  $imageUrl = function (?string $img): string {
      if (! filled($img)) return '/images/news-bg.jpg';
      return str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : '/'.ltrim($img, '/');
  };
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .blog-wrap { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
    .ph { background: #0a0a0a; }

    .blog-lead { padding: 64px 0 0 !important; background: #fff; }
    .blog-lead-card { position:relative; border-radius:20px; overflow:hidden; min-height:480px; display:flex; align-items:flex-end; }
    .blog-lead-bg, .blog-lead-bg img { position:absolute; inset:0; width:100%; height:100%; }
    .blog-lead-bg img { object-fit:cover; object-position:center 30%; }
    .blog-lead-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(10,4,20,.95) 0%,rgba(10,4,20,.55) 50%,transparent 100%); }
    .blog-lead-body { position:relative; z-index:2; padding:48px 52px; max-width:680px; }
    .blog-lead-meta { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .blog-lead-tag { background:var(--o); color:#fff; font-size:.72rem; font-weight:800; padding:4px 14px; border-radius:4px; text-transform:uppercase; letter-spacing:1px; }
    .blog-lead-date { font-size:.8rem; color:rgba(255,255,255,.6); }
    .blog-lead-body h2 { font-size:2.2rem; font-weight:900; color:#fff; line-height:1.2; margin-bottom:14px; }
    .blog-lead-body p { font-size:.97rem; color:rgba(255,255,255,.78); line-height:1.7; margin-bottom:24px; max-width:560px; }
    .blog-lead-btn { display:inline-flex; align-items:center; gap:8px; background:var(--o); color:#fff; font-weight:700; font-size:.92rem; padding:12px 26px; border-radius:8px; }
    .blog-filter { padding:40px 0 8px !important; background:#fff; }
    .blog-cat-nav { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
    .blog-cat-btn { padding:8px 22px; border-radius:50px; font-size:.82rem; font-weight:700; background:#f1f5f9; border:none; color:#475569; }
    .blog-cat-btn.active, .blog-cat-btn:hover { background:var(--dark); color:#fff; }

    .blog-articles { padding: 64px 0 88px !important; background: #fff; }
    .blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

    .blog-card {
      background: #fff; border-radius: 16px; overflow: hidden;
      border: 1px solid #e8edf3; transition: transform .25s, box-shadow .25s;
      display: flex; flex-direction: column; text-decoration: none !important;
    }
    .blog-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(74,26,109,.1); }
    .blog-card-img { height: 200px; overflow: hidden; position: relative; flex-shrink: 0; }
    .blog-card-img img { width: 100%; height: 100%; object-fit: cover; object-position: center top; transition: transform .4s; }
    .blog-card:hover .blog-card-img img { transform: scale(1.05); }
    .blog-card-tag {
      position: absolute; top: 14px; left: 14px;
      background: var(--o); color: #fff; padding: 4px 12px;
      font-size: .68rem; font-weight: 800; text-transform: uppercase;
      border-radius: 4px; letter-spacing: .5px;
    }
    .blog-card-body { padding: 22px 24px 24px; display: flex; flex-direction: column; flex: 1; }
    .blog-card-meta {
      font-size: .76rem; color: #94a3b8; margin-bottom: 10px;
      display: flex; align-items: center; gap: 6px;
    }
    .blog-card-body h3 {
      font-size: 1rem; font-weight: 800; color: var(--dark); line-height: 1.45;
      margin-bottom: 12px;
    }
    .blog-card-body p {
      font-size: .85rem; color: #64748b; line-height: 1.6;
      margin-bottom: 14px; flex: 1;
    }
    .blog-card-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .83rem; font-weight: 700; color: var(--o); transition: gap .2s; margin-top: auto;
    }
    .blog-card-link:hover { gap: 10px; }

    @media (max-width: 900px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 620px) { .blog-grid { grid-template-columns: 1fr; } .blog-wrap { padding: 0 24px; } }
  </style>

  <!-- PAGE HERO -->
  <section class="ph">
    <div class="ph-bg">
      <img loading="eager" decoding="async" src="/images/news-bg.jpg" alt="Blog &amp; Insights">
    </div>
    <div class="ph-overlay"></div>
    <div class="ph-inner">
      <div class="ph-content">
        <span class="ph-eyebrow"><i data-lucide="edit-3"></i> Blog &amp; Insights</span>
        <nav class="ph-breadcrumb" aria-label="Breadcrumb">
          <a href="/">Home</a><i data-lucide="chevron-right"></i><span>Blog &amp; Articles</span>
        </nav>
        <h1>GoCare <em>Insights</em> &amp; Updates</h1>
        <p>Expert articles, student success stories, industry news, and career advice from Kenya's leading healthcare and hospitality training institute.</p>
        <div class="ph-btns">
          <a href="/apply" class="ph-btn-primary">Apply Now <i data-lucide="arrow-right"></i></a>
        </div>
      </div>
    </div>
  </section>

  @if ($posts->isNotEmpty())
    @php $lead = $posts->first(); @endphp
    <section class="blog-lead">
      <div class="blog-wrap">
        <div class="blog-lead-card">
          <div class="blog-lead-bg"><img loading="lazy" decoding="async" src="{{ $imageUrl($lead->image) }}" alt="{{ $lead->title }}"></div>
          <div class="blog-lead-overlay"></div>
          <div class="blog-lead-body">
            <div class="blog-lead-meta"><span class="blog-lead-tag">Featured</span><span class="blog-lead-date">{{ $lead->published_at ? $lead->published_at->format('F j, Y') : 'GoCare Blog' }}</span></div>
            <h2>{{ $lead->title }}</h2>
            @if ($lead->excerpt)<p>{{ $lead->excerpt }}</p>@endif
            <a href="/blog/{{ $lead->slug }}" class="blog-lead-btn">Read Full Story <i data-lucide="arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>
    <section class="blog-filter">
      <div class="blog-wrap"><div class="blog-cat-nav">
        <button class="blog-cat-btn active">All Articles</button>
        <button class="blog-cat-btn">Healthcare</button>
        <button class="blog-cat-btn">Hospitality</button>
        <button class="blog-cat-btn">Career Tips</button>
        <button class="blog-cat-btn">Announcements</button>
      </div></div>
    </section>
  @endif

  <!-- ── ARTICLE GRID ── -->
  <section class="blog-articles">
    <div class="blog-wrap">
      <div class="blog-grid">
        @forelse ($posts->skip(1) as $post)
          <a href="/blog/{{ $post->slug }}" class="blog-card">
            <div class="blog-card-img">
              <img loading="lazy" decoding="async" src="{{ $imageUrl($post->image) }}" alt="{{ $post->title }}">
              <span class="blog-card-tag">Article</span>
            </div>
            <div class="blog-card-body">
              <div class="blog-card-meta"><i data-lucide="clock" style="width:13px;height:13px;"></i> {{ $post->published_at ? $post->published_at->format('d M Y') : 'GoCare Blog' }}</div>
              <h3>{{ $post->title }}</h3>
              @if ($post->excerpt)
                <p>{{ $post->excerpt }}</p>
              @endif
              <span class="blog-card-link">Read More <i data-lucide="arrow-right" style="width:15px;height:15px;"></i></span>
            </div>
          </a>
        @empty
          <p style="grid-column:1 / -1;text-align:center;color:#64748b;padding:48px 0;">Articles will appear here soon.</p>
        @endforelse
      </div>
    </div>
  </section>
@endsection
