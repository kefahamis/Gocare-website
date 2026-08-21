@php
  $metaTitle = 'Schools & Programmes | ' . \App\Models\SeoSetting::current()->site_name;
  $metaDescription = 'Browse the full list of accredited courses and programmes offered at GoCare Training Institute.';

  $imageUrl = function (?string $img): ?string {
      if (! filled($img)) {
          return null;
      }
      return str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : '/'.ltrim($img, '/');
  };

  $catFor = function (?string $school): string {
      $s = mb_strtolower($school ?? '');
      if (str_contains($s, 'diploma')) return 'diploma';
      if (str_contains($s, 'medical') || str_contains($s, 'health')) return 'medical';
      if (str_contains($s, 'hosp')) return 'hospitality';
      if (str_contains($s, 'social') || str_contains($s, 'business')) return 'social';
      if (str_contains($s, 'internat')) return 'international';
      return 'medical';
  };

  $badgeFor = function (string $cat): array {
      return [
          'medical' => ['med', 'Medical'],
          'hospitality' => ['hosp', 'Hospitality'],
          'social' => ['soc', 'Social Sciences'],
          'international' => ['intl', 'International'],
          'diploma' => ['dip', 'Diploma'],
      ][$cat] ?? ['med', 'Medical'];
  };

  $defaultImgFor = function (string $cat): string {
      return [
          'medical' => '/images/Nursing-Assistant.jpg',
          'hospitality' => '/images/Hotel-and-Hospitality-Management.jpg',
          'social' => '/images/Social Work-Community-Development.jpg',
          'international' => '/images/Healthcare-professional.jpg',
          'diploma' => '/images/Nursing-Assistant.jpg',
      ][$cat] ?? '/images/Nursing-Assistant.jpg';
  };

  $iconFor = function (?string $title): string {
      $s = mb_strtolower($title ?? '');
      if (str_contains($s, 'medical') || str_contains($s, 'health')) return 'stethoscope';
      if (str_contains($s, 'hosp')) return 'utensils';
      if (str_contains($s, 'social') || str_contains($s, 'business')) return 'users';
      if (str_contains($s, 'internat')) return 'globe';
      return 'graduation-cap';
  };
@endphp
@extends('layouts.site')

@section('content')
  <style>
    .crs-card {
      background:#fff; border-radius:20px; overflow:hidden;
      display:flex; flex-direction:column;
      text-decoration:none !important;
      border:1px solid #ede9f6;
      transition:transform .25s, box-shadow .25s, border-color .25s;
    }
    .crs-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(100,42,126,.15); border-color:var(--o); }
    .crs-img { width:100%; height:190px; overflow:hidden; }
    .crs-img img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
    .crs-card:hover .crs-img img { transform:scale(1.06); }
    .crs-body { padding:20px 22px 22px; flex:1; display:flex; flex-direction:column; gap:10px; }
    .crs-tags { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .crs-badge { padding:3px 10px; border-radius:50px; font-size:.68rem; font-weight:800; letter-spacing:.5px; text-transform:uppercase; }
    .crs-badge.med  { background:#fde8f0; color:#b5194e; }
    .crs-badge.hosp { background:#fff3e0; color:#b45309; }
    .crs-badge.soc  { background:#e8f0fe; color:#1d4ed8; }
    .crs-badge.intl { background:#e6f4ea; color:#166534; }
    .crs-badge.dip  { background:#f3e8ff; color:#6d28d9; }
    .crs-level { background:#f1f5f9; color:#475569; font-size:.68rem; font-weight:700; padding:3px 10px; border-radius:50px; }
    .crs-dur { display:flex; align-items:center; gap:4px; color:#64748b; font-size:.72rem; font-weight:600; margin-left:auto; }
    .crs-body h3 { font-size:1rem; font-weight:800; color:var(--dark); line-height:1.35; margin:0; }
    .crs-body p { font-size:.85rem; color:#64748b; line-height:1.6; margin:0; flex:1; }
    .crs-cta { display:inline-flex; align-items:center; gap:6px; color:var(--o); font-size:.82rem; font-weight:700; margin-top:4px; }
    .crs-tab {
      display:inline-flex; align-items:center; gap:6px;
      padding:9px 20px; border-radius:50px; border:2px solid #e2d9f3;
      background:#fff; color:#64748b; font-weight:700; font-size:.82rem;
      cursor:pointer; transition:.2s;
    }
    .crs-tab:hover { border-color:var(--p); color:var(--p); }
    .crs-tab.active { background:var(--p); border-color:var(--p); color:#fff; }
    .crs-tab .lucide { width:14px; height:14px; }
    .crs-card.hidden { display:none; }
    .crs-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
    @media(max-width:1024px){ .crs-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:600px){ .crs-grid{ grid-template-columns:1fr; } }
    .ph { background: #0a0a0a; }

    .ac-schools-sec { padding: 56px 0 64px; }
    .ac-schools-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; }
    .ac-school-item {
      position: relative; border-radius: 20px; overflow: hidden;
      height: 300px; display: flex; flex-direction: column;
      justify-content: flex-end; text-decoration: none !important;
      transition: transform .35s ease, box-shadow .35s ease;
      box-shadow: 0 8px 30px rgba(0,0,0,.18);
    }
    .ac-school-item:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,.28); }
    .ac-school-item img {
      position: absolute; inset: 0; width: 100%; height: 100%;
      object-fit: cover; object-position: center;
      transition: transform .5s ease;
    }
    .ac-school-item:hover img { transform: scale(1.07); }
    .ac-school-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(10,5,20,.88) 0%, rgba(10,5,20,.4) 55%, transparent 100%);
      transition: background .35s;
    }
    .ac-school-item:hover .ac-school-overlay {
      background: linear-gradient(to top, rgba(74,26,109,.9) 0%, rgba(236,116,36,.35) 60%, transparent 100%);
    }
    .ac-school-body { position: relative; z-index: 2; padding: 24px 22px 22px; }
    .ac-school-icon {
      width: 44px; height: 44px; border-radius: 12px;
      background: rgba(255,255,255,.15);
      backdrop-filter: blur(8px);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 12px; color: #fff;
    }
    .ac-school-icon .lucide { width: 22px; height: 22px; stroke: #fff; }
    .ac-school-item h4 {
      font-family: 'Outfit', sans-serif; font-size: 1.05rem;
      font-weight: 800; color: #fff; line-height: 1.25;
      margin-bottom: 10px;
    }
    .ac-school-btn {
      display: inline-flex; align-items: center; gap: 6px;
      background: #ec7424; color: #fff;
      padding: 7px 16px; border-radius: 50px;
      font-size: .78rem; font-weight: 700;
      transition: background .2s;
    }
    .ac-school-item:hover .ac-school-btn { background: #fff; color: #ec7424; }
    @media(max-width:1024px){ .ac-schools-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:560px){ .ac-schools-grid{ grid-template-columns:1fr; } }

    .ac-photo-strip {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      height: 220px; gap: 8px;
      border-radius: 20px; overflow: hidden;
      margin: 0 0 64px;
      position: relative;
    }
    .ac-photo-strip img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ac-strip-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to right, rgba(10,5,20,.7) 0%, transparent 45%);
      display: flex; align-items: center; padding: 0 40px;
      pointer-events: none;
    }
    .ac-strip-text { color: #fff; }
    .ac-strip-text strong {
      display: block; font-family: 'Outfit', sans-serif;
      font-size: 1.4rem; font-weight: 800; line-height: 1.2;
      margin-bottom: 6px;
    }
    .ac-strip-text span { font-size: .85rem; color: rgba(255,255,255,.75); }
    @media(max-width:768px){ .ac-photo-strip{ grid-template-columns:1fr 1fr; height:180px;} .ac-photo-strip img:last-child{ display:none; } }
  </style>

  <!-- PAGE HERO -->
  <section class="ph ph--split">
    <div class="ph-bg">
      <img loading="eager" decoding="async" src="/images/new-images/courses/hero-courses.jpeg" alt="Explore Our Programmes">
    </div>
    <div class="ph-overlay"></div>
    <div class="ph-inner">
      <div class="ph-content">
        <span class="ph-eyebrow"><i data-lucide="search"></i> Explore Our Programmes</span>
        <nav class="ph-breadcrumb" aria-label="Breadcrumb">
          <a href="/">Home</a><i data-lucide="chevron-right"></i><span>Courses</span>
        </nav>
        <h1>View All <em>Courses</em></h1>
        <p>Browse GoCare&#8217;s full range of certificate and diploma programmes in healthcare, hospitality, social sciences, and internationally recognised certifications.</p>
        <div class="ph-btns">
          <a href="#all-courses" class="ph-btn-primary">Browse Courses <i data-lucide="arrow-right"></i></a>
          <a href="/apply" class="ph-btn-ghost">Apply Now <i data-lucide="arrow-right"></i></a>
        </div>
      </div>
      <div class="ph-form-card">
        <h3>Find Your Course</h3>
        <p>Filter by school, level, or duration.</p>
        <div class="ph-form-group">
          <label>School / Category</label>
          <select class="ph-form-input">
            <option value="all">All Schools</option>
            <option value="med">Medical &amp; Health Sciences</option>
            <option value="hosp">Hospitality Management</option>
            <option value="social">Social Sciences &amp; Business</option>
            <option value="intl">International Certifications</option>
          </select>
        </div>
        <div class="ph-form-group">
          <label>Study Duration</label>
          <select class="ph-form-input">
            <option value="">Any duration</option>
            <option>6 Months</option>
            <option>1 Year</option>
            <option>2 Years</option>
          </select>
        </div>
        <button class="ph-form-submit" onclick="document.getElementById('all-courses')?.scrollIntoView({behavior:'smooth'})">Browse Courses &rarr;</button>
      </div>
    </div>
  </section>

  <div class="ac-page">

    @if (($schools ?? collect())->isNotEmpty())
      <section class="ac-schools-sec">
        <div class="wrap">
          <div class="ac-schools-grid">
            @foreach ($schools as $school)
              @php $sIcon = $iconFor($school->title); $sImg = $imageUrl($school->image) ?? $defaultImgFor($catFor($school->title)); @endphp
              <a href="/schools/{{ $school->slug }}" class="ac-school-item">
                <img loading="lazy" decoding="async" src="{{ $sImg }}" alt="{{ $school->title }}">
                <div class="ac-school-overlay"></div>
                <div class="ac-school-body">
                  <div class="ac-school-icon"><i data-lucide="{{ $sIcon }}"></i></div>
                  <h4>{{ $school->title }}</h4>
                  <span class="ac-school-btn">View Courses <i data-lucide="arrow-right"></i></span>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    <div class="wrap">
      <div class="ac-photo-strip">
        <img loading="lazy" decoding="async" src="/images/new-images/courses/Gallery-1.jpeg" alt="GoCare students">
        <img loading="lazy" decoding="async" src="/images/new-images/courses/Gallery-2.jpeg" alt="GoCare practical">
        <img loading="lazy" decoding="async" src="/images/new-images/courses/Gallery-3.jpeg" alt="GoCare campus">
        <img loading="lazy" decoding="async" src="/images/new-images/courses/Gallery-4.jpeg" alt="GoCare graduates">
        <div class="ac-strip-overlay">
          <div class="ac-strip-text">
            <strong>Real Training.<br>Real Results.</strong>
            <span>Industry-aligned programmes across {{ ($schools ?? collect())->isNotEmpty() ? $schools->count() : 4 }} schools</span>
          </div>
        </div>
      </div>
    </div>

    <section id="all-courses" style="padding:64px 0 80px;background:#f8f6fc;">
      <div class="wrap">

        <div style="text-align:center;margin-bottom:40px;">
          <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(100,42,126,.1);color:var(--p);padding:6px 18px;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;"><i data-lucide="layout-grid" style="width:14px;height:14px;"></i> All Programmes</div>
          <h2 style="font-size:2.4rem;font-weight:900;color:var(--dark);margin-bottom:12px;">Browse Our <em style="color:var(--o);font-style:normal;">Courses</em></h2>
          <p style="color:#64748b;max-width:540px;margin:0 auto;font-size:1rem;">Practical, accredited programmes across healthcare, hospitality, social sciences &amp; international certifications.</p>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:40px;" id="courseTabs">
          <button class="crs-tab active" data-filter="all">All Courses</button>
          <button class="crs-tab" data-filter="medical"><i data-lucide="stethoscope"></i> Medical &amp; Health</button>
          <button class="crs-tab" data-filter="hospitality"><i data-lucide="utensils"></i> Hospitality</button>
          <button class="crs-tab" data-filter="social"><i data-lucide="users"></i> Social Sciences</button>
          <button class="crs-tab" data-filter="international"><i data-lucide="globe"></i> International</button>
          <button class="crs-tab" data-filter="diploma"><i data-lucide="award"></i> Diplomas</button>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
          <p style="margin:0;color:#64748b;font-size:.9rem;"><span id="crsCount" style="font-weight:700;color:var(--dark);">{{ $courses->count() }}</span> programmes available</p>
          <a href="/apply" style="display:inline-flex;align-items:center;gap:8px;background:var(--o);color:#fff;padding:10px 24px;border-radius:50px;font-weight:700;font-size:.88rem;text-decoration:none;">Apply Now <i data-lucide="arrow-right" style="width:16px;height:16px;"></i></a>
        </div>

        <div id="crsGrid" class="crs-grid">
          @forelse ($courses as $course)
            @php
              $cat = $catFor($course->school);
              [$cls, $label] = $badgeFor($cat);
              $courseImg = $imageUrl($course->image) ?? $defaultImgFor($cat);
            @endphp
            <a href="/courses/{{ $course->slug }}" class="crs-card" data-cat="{{ $cat }}">
              <div class="crs-img"><img loading="lazy" decoding="async" src="{{ $courseImg }}" alt="{{ $course->title }}"></div>
              <div class="crs-body">
                <div class="crs-tags"><span class="crs-badge {{ $cls }}">{{ $label }}</span></div>
                <h3>{{ $course->title }}</h3>
                <p>{{ $course->summary }}</p>
                <span class="crs-cta">Apply Now <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></span>
              </div>
            </a>
          @empty
            <p style="grid-column:1 / -1;text-align:center;color:#64748b;padding:48px 0;">Programmes will appear here soon.</p>
          @endforelse
        </div>

        <p id="crsEmpty" style="display:none;text-align:center;padding:48px 0;color:#64748b;font-size:1rem;">No courses found for this filter.</p>

      </div>
    </section>

  </div><!-- /ac-page -->

  <script>
    (function () {
      var tabs  = document.querySelectorAll('.crs-tab');
      var cards = document.querySelectorAll('.crs-card');
      var count = document.getElementById('crsCount');
      var empty = document.getElementById('crsEmpty');

      function filter(cat) {
        var visible = 0;
        cards.forEach(function (c) {
          var cats = (c.dataset.cat || '').split(' ');
          var show = cat === 'all' || cats.indexOf(cat) !== -1;
          c.classList.toggle('hidden', !show);
          if (show) visible++;
        });
        if (count) count.textContent = visible;
        if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.classList.remove('active'); });
          tab.classList.add('active');
          filter(tab.dataset.filter);
        });
      });
    })();
  </script>
@endsection
