<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="{{ asset('images/gocare-institute-logo.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/gocare-institute-logo.png') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ ($frontend['site']['name'] ?? config('frontend.site.name')) }} | Gallery</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  @include('components.seo')
  <style>
    /* ── Gallery page ── */
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; overflow-x: hidden; }
    body { font-family: "Plus Jakarta Sans", sans-serif; background: #0d0018; color: #fff; }
    a { color: inherit; text-decoration: none; }
    .wrap { max-width: 1260px; margin: 0 auto; padding: 0 24px; }
    .gallery-shell { min-height: 100vh; }
    .gal-hero {
      padding: 120px 0 80px;
      text-align: center;
      position: relative;
      overflow: hidden;
      background: #0d0018;
    }
    .gal-hero-bg {
      position: absolute; inset: 0;
      background: url('{{ asset('images/new-images/Collage-fit.png') }}') center center / cover no-repeat;
      transform: scale(1.04);
      transition: transform 8s ease-out;
    }
    .gal-hero:hover .gal-hero-bg { transform: scale(1); }
    .gal-hero::before {
      content: '';
      position: absolute; inset: 0; z-index: 1;
      background:
        linear-gradient(135deg, rgba(74,26,109,.78) 0%, rgba(45,10,78,.65) 50%, rgba(26,0,48,.82) 100%),
        radial-gradient(ellipse at 20% 60%, rgba(236,116,36,.18) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 20%, rgba(100,42,126,.22) 0%, transparent 55%);
    }
    .gal-hero::after {
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0; height: 80px; z-index: 1;
      background: linear-gradient(to bottom, transparent, #0d0018);
    }
    .gal-hero-inner { position: relative; z-index: 2; max-width: 700px; margin: 0 auto; padding: 0 24px; }
    .gal-hero-label { display: inline-flex; align-items: center; gap: 8px; background: rgba(236,116,36,.15); border: 1px solid rgba(236,116,36,.3); color: var(--o); font-size: .8rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; padding: 6px 16px; border-radius: 50px; margin-bottom: 20px; }
    .gal-hero h1 { font-size: clamp(2rem, 5vw, 3rem); font-weight: 900; color: #fff; margin: 0 0 16px; line-height: 1.15; }
    .gal-hero h1 span { color: var(--o); }
    .gal-hero p { color: rgba(255,255,255,.7); font-size: 1.05rem; line-height: 1.7; margin: 0; }
    .gal-toolbar {
      background: #0d0018;
      border-bottom: 1px solid rgba(255,255,255,.06);
      position: sticky; top: 70px; z-index: 100;
    }
    .gal-toolbar-inner {
      max-width: 1260px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; gap: 0;
    }
    .gal-tabs-scroll {
      flex: 1; display: flex; gap: 2px; overflow-x: auto; scrollbar-width: none;
    }
    .gal-tabs-scroll::-webkit-scrollbar { display: none; }
    .gal-tab {
      flex-shrink: 0; padding: 16px 18px;
      font-size: .82rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
      color: rgba(255,255,255,.4);
      background: transparent; border: none;
      border-bottom: 2px solid transparent;
      border-radius: 0 !important;
      cursor: pointer;
      transition: color .2s, border-color .2s;
      white-space: nowrap;
    }
    .gal-tab:hover { color: rgba(255,255,255,.8); }
    .gal-tab.active { color: var(--o); border-bottom-color: var(--o); }
    .gal-layouts {
      display: flex; gap: 4px; padding: 0 0 0 20px;
      border-left: 1px solid rgba(255,255,255,.08); margin-left: 8px;
      flex-shrink: 0;
    }
    .gal-layout-btn {
      width: 34px; height: 34px; border-radius: 6px;
      background: transparent; border: 1px solid transparent;
      color: rgba(255,255,255,.3); cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background .2s, color .2s, border-color .2s;
    }
    .gal-layout-btn:hover { color: rgba(255,255,255,.7); background: rgba(255,255,255,.06); }
    .gal-layout-btn.active { color: var(--o); border-color: rgba(236,116,36,.4); background: rgba(236,116,36,.1); }
    .gal-layout-btn svg { width: 16px; height: 16px; fill: currentColor; }
    .gal-section { padding: 0 0 80px; background: #0d0018; }
    .gal-wrap { max-width: 1260px; margin: 0 auto; padding: 0 24px; }
    .gal-meta {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 0 16px;
    }
    .gal-count { font-size: .82rem; color: rgba(255,255,255,.35); letter-spacing: .04em; text-transform: uppercase; }
    .gal-count strong { color: rgba(255,255,255,.7); }
    .gal-grid { gap: 10px; }
    .gal-grid.layout-masonry { columns: 4; column-gap: 10px; display: block; }
    .gal-grid.layout-grid    { display: grid; grid-template-columns: repeat(4,1fr); }
    .gal-grid.layout-wide    { display: grid; grid-template-columns: repeat(2,1fr); }
    .gal-grid.layout-strip   { display: grid; grid-template-columns: repeat(6,1fr); }
    .gal-item {
      overflow: hidden; position: relative; cursor: pointer;
      background: #1a0a2e;
      border-radius: 4px;
    }
    @keyframes gal-shimmer {
      0%   { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    .gal-item:not(.loaded) {
      background: linear-gradient(100deg, #1a0a2e 30%, #33194f 50%, #1a0a2e 70%);
      background-size: 200% 100%;
      animation: gal-shimmer 1.5s ease-in-out infinite;
    }
    .layout-masonry .gal-item:not(.loaded) { min-height: 240px; }
    .layout-masonry .gal-item { break-inside: avoid; margin-bottom: 10px; }
    .layout-grid .gal-item    { aspect-ratio: 4/3; }
    .layout-wide .gal-item    { aspect-ratio: 16/10; }
    .layout-strip .gal-item   { aspect-ratio: 1; }
    .gal-item img {
      width: 100%; height: 100%;
      object-fit: cover; display: block;
      opacity: 0;
      transition: transform .6s cubic-bezier(.25,.46,.45,.94), filter .6s, opacity .5s ease;
      filter: brightness(.88) saturate(.9);
    }
    .gal-item.loaded img { opacity: 1; }
    .layout-masonry .gal-item img { height: auto; }
    .gal-item:hover img {
      transform: scale(1.07);
      filter: brightness(1) saturate(1.1);
    }
    .gal-item-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(160deg, transparent 40%, rgba(10,0,30,.85) 100%);
      opacity: 0;
      transition: opacity .4s;
      display: flex; flex-direction: column; justify-content: flex-end;
      padding: 18px;
    }
    .gal-item:hover .gal-item-overlay { opacity: 1; }
    .gal-item-overlay::before {
      content: '';
      position: absolute; top: 14px; right: 14px;
      width: 30px; height: 30px;
      background: rgba(255,255,255,.15) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3Cline x1='11' y1='8' x2='11' y2='14'/%3E%3Cline x1='8' y1='11' x2='14' y2='11'/%3E%3C/svg%3E") center/16px no-repeat;
      border-radius: 50%;
      backdrop-filter: blur(4px);
      transform: scale(0); transition: transform .3s .05s;
    }
    .gal-item:hover .gal-item-overlay::before { transform: scale(1); }
    .gal-item-label {
      color: #fff; font-size: .72rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase;
      background: rgba(236,116,36,.25); border: 1px solid rgba(236,116,36,.4);
      padding: 3px 10px; border-radius: 20px; width: fit-content;
      backdrop-filter: blur(6px);
      transform: translateY(6px); transition: transform .35s;
    }
    .gal-item:hover .gal-item-label { transform: translateY(0); }
    .gal-item.hidden { display: none; }
    .gal-lb {
      position: fixed; inset: 0; z-index: 9999;
      display: flex; align-items: center; justify-content: center;
      background: rgba(4,0,12,0);
      backdrop-filter: blur(0px);
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transition:
        opacity .5s cubic-bezier(.4,0,.2,1),
        background .5s cubic-bezier(.4,0,.2,1),
        backdrop-filter .5s cubic-bezier(.4,0,.2,1),
        visibility 0s linear .5s;
    }
    .gal-lb.open {
      opacity: 1;
      visibility: visible;
      pointer-events: all;
      background: rgba(4,0,12,.96);
      backdrop-filter: blur(20px);
      transition:
        opacity .5s cubic-bezier(.4,0,.2,1),
        background .5s cubic-bezier(.4,0,.2,1),
        backdrop-filter .5s cubic-bezier(.4,0,.2,1),
        visibility 0s linear 0s;
    }
    .gal-lb-inner {
      position: relative; max-width: 88vw; max-height: 88vh;
      display: flex; align-items: center; justify-content: center;
    }
    .gal-lb-inner img {
      max-width: 88vw; max-height: 84vh;
      border-radius: 8px; object-fit: contain; display: block;
      box-shadow: 0 48px 120px rgba(0,0,0,.9), 0 0 0 1px rgba(255,255,255,.04);
      transition: opacity .45s cubic-bezier(.4,0,.2,1),
                  transform .45s cubic-bezier(.4,0,.2,1),
                  filter .45s cubic-bezier(.4,0,.2,1);
    }
    .gal-lb-inner img.lb-enter { opacity: 0; transform: scale(.97); filter: blur(4px); }
    .gal-lb-inner img.lb-active { opacity: 1; transform: scale(1);   filter: blur(0); }
    .gal-lb-inner img.lb-exit  { opacity: 0; transform: scale(1.03); filter: blur(4px); position: absolute; top: 0; left: 0; right: 0; bottom: 0; margin: auto; pointer-events: none; }
    .gal-lb.open .gal-lb-inner { animation: lbInnerIn .45s cubic-bezier(.34,1.56,.64,1) both; }
    @keyframes lbInnerIn {
      from { opacity:0; transform: scale(.93) translateY(18px); }
      to   { opacity:1; transform: scale(1)   translateY(0); }
    }
    .gal-lb-close {
      position: fixed; top: 24px; right: 24px;
      width: 42px; height: 42px; border-radius: 50%;
      background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
      color: #fff; font-size: 1.3rem; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      backdrop-filter: blur(12px);
      transition: background .3s, transform .4s cubic-bezier(.34,1.56,.64,1), border-color .3s, opacity .3s;
      opacity: 0;
    }
    .gal-lb.open .gal-lb-close { opacity: 1; transition-delay: .2s, 0s, 0s, .2s; }
    .gal-lb-close:hover { background: rgba(236,116,36,.55); border-color: rgba(236,116,36,.5); transform: rotate(90deg) scale(1.1); }
    .gal-lb-prev, .gal-lb-next {
      position: fixed; top: 50%; transform: translateY(-50%);
      width: 52px; height: 52px; border-radius: 50%;
      background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
      color: #fff; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      backdrop-filter: blur(12px);
      transition: background .3s, border-color .3s, transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
      opacity: 0;
    }
    .gal-lb.open .gal-lb-prev,
    .gal-lb.open .gal-lb-next { opacity: 1; transition-delay: .25s, 0s, 0s, .25s; }
    .gal-lb-prev:hover { background: rgba(236,116,36,.45); border-color: rgba(236,116,36,.5); transform: translateY(-50%) translateX(-4px) scale(1.08); }
    .gal-lb-next:hover { background: rgba(236,116,36,.45); border-color: rgba(236,116,36,.5); transform: translateY(-50%) translateX(4px)  scale(1.08); }
    .gal-lb-prev { left: 24px; }
    .gal-lb-next { right: 24px; }
    .gal-lb-counter {
      position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
      color: rgba(255,255,255,.35); font-size: .75rem; letter-spacing: .14em;
      text-transform: uppercase; background: rgba(255,255,255,.06);
      padding: 5px 14px; border-radius: 20px; border: 1px solid rgba(255,255,255,.08);
      backdrop-filter: blur(8px);
      opacity: 0; transition: opacity .3s;
    }
    .gal-lb.open .gal-lb-counter { opacity: 1; transition-delay: .3s; }
    .gal-cta { background: linear-gradient(135deg, #0d0018, #1a0030 50%, #0d0018); padding: 70px 24px; text-align: center; border-top: 1px solid rgba(255,255,255,.06); }
    .gal-cta h2 { font-size: 2rem; font-weight: 900; color: #fff; margin: 0 0 12px; }
    .gal-cta p { color: rgba(255,255,255,.5); margin: 0 0 32px; }
    .gal-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .gal-cta-btn { padding: 14px 30px; border-radius: 50px; font-weight: 700; font-size: .95rem; text-decoration: none; transition: transform .2s, box-shadow .2s; display: inline-flex; align-items: center; gap: 8px; }
    .gal-cta-btn--o { background: var(--o); color: #fff; box-shadow: 0 8px 28px rgba(236,116,36,.4); }
    .gal-cta-btn--o:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(236,116,36,.5); }
    .gal-cta-btn--ghost { background: rgba(255,255,255,.07); color: #fff; border: 1px solid rgba(255,255,255,.2); }
    .gal-cta-btn--ghost:hover { background: rgba(255,255,255,.14); }
    .gal-pagination { display: flex; align-items: center; justify-content: center; gap: 5px; padding: 40px 0 0; flex-wrap: wrap; }
    .gal-pg-btn {
      min-width: 36px; height: 36px; padding: 0 10px;
      border-radius: 6px; border: 1px solid rgba(255,255,255,.1);
      background: rgba(255,255,255,.05); color: rgba(255,255,255,.5);
      font-size: .85rem; font-weight: 600; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background .2s, color .2s, border-color .2s;
    }
    .gal-pg-btn:hover { background: rgba(236,116,36,.15); border-color: rgba(236,116,36,.4); color: var(--o); }
    .gal-pg-btn.active { background: var(--o); color: #fff; border-color: var(--o); }
    .gal-pg-btn:disabled { opacity: .25; cursor: default; pointer-events: none; }
    .gal-pg-ellipsis { color: rgba(255,255,255,.25); font-size: .9rem; padding: 0 4px; line-height: 36px; }
    @media (max-width: 1024px) {
      .gal-grid.layout-masonry { columns: 3; }
      .gal-grid.layout-grid    { grid-template-columns: repeat(3,1fr); }
      .gal-grid.layout-strip   { grid-template-columns: repeat(4,1fr); }
    }
    @media (max-width: 640px) {
      .gal-grid.layout-masonry { columns: 2; }
      .gal-grid.layout-grid    { grid-template-columns: repeat(2,1fr); }
      .gal-grid.layout-wide    { grid-template-columns: 1fr; }
      .gal-grid.layout-strip   { grid-template-columns: repeat(3,1fr); }
      .gal-lb-prev { left: 8px; } .gal-lb-next { right: 8px; }
    }
    @media (max-width: 400px) {
      .gal-grid.layout-masonry { columns: 1; }
      .gal-grid.layout-strip   { grid-template-columns: repeat(2,1fr); }
    }
  </style>
</head>
<body>
  @php($frontend = $frontend ?? config('frontend'))
  <!-- -- NAVBAR ---------------------------------------- -->
  <nav class="nav" id="mainNav">
    <div class="nav-inner">
      <a href="index.html" class="logo">
        <img loading="eager" decoding="async" src="{{ asset('images/gocare-institute-logo.png') }}" width="100" />
      </a>
      <div class="nav-links">
        <div class="nav-item"><a href="index.html">Home</a></div>
        <div class="nav-item">
          <a href="about.html">About GoCare <i data-lucide="chevron-down"></i></a>
          <div class="dropdown about-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="info"></i></span>
              <div class="res-hd-text"><strong>About GoCare</strong><small>Our story, values &amp; recognition</small></div>
            </div>
            <div class="res-body">
              <a href="about.html"><span class="rm-ico rm-ico--indigo"><i data-lucide="home"></i></span><span class="rm-text"><strong>About GoCare Institute</strong><small>Our history &amp; leadership</small></span></a>
              <a href="about.html#vision"><span class="rm-ico rm-ico--teal"><i data-lucide="flag"></i></span><span class="rm-text"><strong>Mission &amp; Vision</strong><small>What drives us forward</small></span></a>
              <a href="about.html#core-values"><span class="rm-ico rm-ico--rose"><i data-lucide="heart"></i></span><span class="rm-text"><strong>Core Values</strong><small>Principles we live by</small></span></a>
              <a href="about/accreditation.html"><span class="rm-ico rm-ico--purple"><i data-lucide="globe"></i></span><span class="rm-text"><strong>Accreditation &amp; Recognition</strong><small>Nationally &amp; internationally recognised</small></span></a>
              <a href="about/why-choose-us.html"><span class="rm-ico rm-ico--amber"><i data-lucide="check-circle"></i></span><span class="rm-text"><strong>Why Choose GoCare</strong><small>Stand-out reasons to join us</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="courses.html">Schools &amp; Programs <i data-lucide="chevron-down"></i></a>
          <div class="dropdown schools-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="graduation-cap"></i></span>
              <div class="res-hd-text"><strong>Schools &amp; Programs</strong><small>Explore our diverse range of courses</small></div>
            </div>
            <div class="res-body">
              <a href="schools/medical-health-sciences.html"><span class="rm-ico rm-ico--rose"><i data-lucide="stethoscope"></i></span><span class="rm-text"><strong>School of Medical &amp; Health Sciences</strong><small>Healthcare &amp; clinical programs</small></span></a>
              <a href="schools/hospitality-management.html"><span class="rm-ico rm-ico--amber"><i data-lucide="utensils"></i></span><span class="rm-text"><strong>School of Hospitality Management</strong><small>Tourism, food &amp; front office</small></span></a>
              <a href="schools/social-sciences-business.html"><span class="rm-ico rm-ico--indigo"><i data-lucide="users"></i></span><span class="rm-text"><strong>School of Social Sciences &amp; Business</strong><small>Community &amp; business programs</small></span></a>
              <a href="schools/international-certifications.html"><span class="rm-ico rm-ico--teal"><i data-lucide="globe"></i></span><span class="rm-text"><strong>International Certifications</strong><small>Globally recognised qualifications</small></span></a>
              <a href="courses.html"><span class="rm-ico rm-ico--purple"><i data-lucide="search"></i></span><span class="rm-text"><strong>View All Courses</strong><small>Browse the full programme list</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="admissions.html#overview">Admissions <i data-lucide="chevron-down"></i></a>
          <div class="dropdown admissions-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="clipboard-list"></i></span>
              <div class="res-hd-text"><strong>Admissions</strong><small>Your journey to GoCare starts here</small></div>
            </div>
            <div class="res-body">
              <a href="admissions.html#overview"><span class="rm-ico rm-ico--indigo"><i data-lucide="info"></i></span><span class="rm-text"><strong>Admissions Overview</strong><small>Everything you need to know</small></span></a>
              <a href="admissions.html#how-to-apply"><span class="rm-ico rm-ico--teal"><i data-lucide="file-edit"></i></span><span class="rm-text"><strong>How to Apply</strong><small>Step-by-step application guide</small></span></a>
              <a href="admissions.html#intakes"><span class="rm-ico rm-ico--amber"><i data-lucide="calendar"></i></span><span class="rm-text"><strong>Intakes &amp; Deadlines</strong><small>Upcoming intake dates</small></span></a>
              <a href="admissions.html#requirements"><span class="rm-ico rm-ico--orange"><i data-lucide="clipboard-list"></i></span><span class="rm-text"><strong>Entry Requirements</strong><small>Academic &amp; age criteria</small></span></a>
              <a href="admissions.html#fees"><span class="rm-ico rm-ico--purple"><i data-lucide="wallet"></i></span><span class="rm-text"><strong>Fees &amp; Payment Options</strong><small>Tuition, HELB &amp; bursaries</small></span></a>
              <a href="admissions.html#support"><span class="rm-ico rm-ico--rose"><i data-lucide="headphones"></i></span><span class="rm-text"><strong>Admissions Support</strong><small>Get help from our team</small></span></a>
              <a href="modes-of-study.html"><span class="rm-ico rm-ico--teal"><i data-lucide="book-open"></i></span><span class="rm-text"><strong>Modes of Study</strong><small>Full-time, part-time &amp; online</small></span></a>
              <a href="hostels-and-accommodation.html"><span class="rm-ico rm-ico--amber"><i data-lucide="bed"></i></span><span class="rm-text"><strong>Hostels &amp; Accommodation</strong><small>Comfortable student housing</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="industry-liaison.html">Industry Liaison <i data-lucide="chevron-down"></i></a>
          <div class="dropdown industry-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="briefcase"></i></span>
              <div class="res-hd-text"><strong>Industry Liaison</strong><small>Partnerships, internships &amp; career services</small></div>
            </div>
            <div class="res-body">
              <a href="industry-liaison.html#our-role"><span class="rm-ico rm-ico--indigo"><i data-lucide="globe"></i></span><span class="rm-text"><strong>Our Role</strong><small>Bridging education &amp; industry</small></span></a>
              <a href="industrial-attachment.html"><span class="rm-ico rm-ico--orange"><i data-lucide="clipboard-list"></i></span><span class="rm-text"><strong>Internship &amp; Attachment</strong><small>Hands-on industry placements</small></span></a>
              <a href="institutional-partnerships.html"><span class="rm-ico rm-ico--teal"><i data-lucide="handshake"></i></span><span class="rm-text"><strong>Institutional Partnerships</strong><small>Our network of employers</small></span></a>
              <a href="careers.html"><span class="rm-ico rm-ico--amber"><i data-lucide="briefcase"></i></span><span class="rm-text"><strong>Career Services</strong><small>Jobs, placements &amp; guidance</small></span></a>
              <a href="courses/amca-usa-certification.html"><span class="rm-ico rm-ico--purple"><i data-lucide="globe-2"></i></span><span class="rm-text"><strong>International Certification</strong><small>USA &amp; global qualifications</small></span></a>
              <a href="alumni-network.html"><span class="rm-ico rm-ico--rose"><i data-lucide="users"></i></span><span class="rm-text"><strong>Alumni Network</strong><small>Stay connected after graduation</small></span></a>
              <a href="home-based-care.html"><span class="rm-ico rm-ico--teal"><i data-lucide="heart-pulse"></i></span><span class="rm-text"><strong>GoCare Health Solutions</strong><small>Professional healthcare services</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="#">Resources <i data-lucide="chevron-down"></i></a>
          <div class="dropdown resources-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="layers"></i></span>
              <div class="res-hd-text"><strong>Resources &amp; Tools</strong><small>Everything you need to succeed at GoCare</small></div>
            </div>
            <div class="res-body">
              <a href="student-resources.html"><span class="rm-ico rm-ico--purple"><i data-lucide="book-open"></i></span><span class="rm-text"><strong>Student Resources</strong><small>Study materials &amp; guides</small></span></a>
              <a href="downloads.html"><span class="rm-ico rm-ico--orange"><i data-lucide="download"></i></span><span class="rm-text"><strong>Downloads</strong><small>Forms, brochures &amp; prospectus</small></span></a>
              <a href="blog.html"><span class="rm-ico rm-ico--amber"><i data-lucide="edit-3"></i></span><span class="rm-text"><strong>Blogs &amp; Articles</strong><small>Insights, tips &amp; news</small></span></a>
              <a href="#"><span class="rm-ico rm-ico--indigo"><i data-lucide="graduation-cap"></i></span><span class="rm-text"><strong>Student Portal</strong><small>Access your student account</small></span></a>
              <a href="student-testimonials-success-stories.html"><span class="rm-ico rm-ico--teal"><i data-lucide="message-circle"></i></span><span class="rm-text"><strong>Success Stories</strong><small>Graduate testimonials</small></span></a>
              <a href="gallery.html"><span class="rm-ico rm-ico--rose"><i data-lucide="images"></i></span><span class="rm-text"><strong>Photo Gallery</strong><small>Life &amp; moments at GoCare</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item"><a href="contact.html">Contact Us</a></div>
      </div>
      <div class="nav-right">
        <a href="apply.html" class="nav-apply-btn">Start Your Journey <i data-lucide="arrow-right"></i></a>
        <div class="ham" id="hamBtn"><span></span><span></span><span></span></div>
      </div>
    </div>
    <div class="mob-menu" id="mobMenu">
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="courses.html">Schools &amp; Programs</a>
      <a href="admissions.html#overview">Admissions</a>
      <a href="industry-liaison.html">Industry Liaison</a>
      <a href="faqs.html">FAQs</a>
      <a href="downloads.html">Downloads</a>
      <a href="home-based-care.html">Home Based Care</a>
      <a href="gallery.html">Gallery</a>
      <a href="events.html">Events &amp; Open Days</a>
      <a href="blog.html">Blog &amp; Articles</a>
      <a href="contact.html">Contact Us</a>
      <a href="apply.html" class="btn btn-primary mob-cta">Start Your Journey <i data-lucide="arrow-right"></i></a>
    </div>
  </nav>

  <div class="subbar">
    <div class="wrap subbar-inner">
      <span class="subbar-tagline">Train With The Experts... Become an Expert!</span>
      <div class="subbar-portals">
        <a href="#" class="subbar-btn subbar-btn--student">Student Portal</a>
        <a href="#" class="subbar-btn subbar-btn--staff">Staff Portal</a>
      </div>
    </div>
  </div>

  <main class="gallery-shell">
    <section class="gal-hero">
      <div class="gal-hero-bg"></div>
      <div class="gal-hero-inner">
        <div class="gal-hero-label"><i data-lucide="images" style="width:14px;height:14px"></i> Photo Gallery</div>
        <h1>Life at <span>GoCare</span></h1>
        <p>Moments from our classrooms, clinical labs, events and campus life &mdash; capturing the GoCare experience.</p>
      </div>
    </section>

    <div class="gal-toolbar">
      <div class="gal-toolbar-inner">
        <div class="gal-tabs-scroll">
          <button class="gal-tab active" data-cat="all">All Photos</button>
          <button class="gal-tab" data-cat="campus">Campus Life</button>
          <button class="gal-tab" data-cat="clinical">Clinical Training</button>
          <button class="gal-tab" data-cat="events">Events &amp; Ceremonies</button>
          <button class="gal-tab" data-cat="students">Students</button>
          <button class="gal-tab" data-cat="community">Community</button>
        </div>
        <div class="gal-layouts">
          <button class="gal-layout-btn active" data-layout="layout-masonry" title="Masonry">
            <svg viewBox="0 0 16 16"><rect x="0" y="0" width="7" height="5" rx="1"/><rect x="0" y="6" width="7" height="10" rx="1"/><rect x="9" y="0" width="7" height="9" rx="1"/><rect x="9" y="10" width="7" height="6" rx="1"/></svg>
          </button>
          <button class="gal-layout-btn" data-layout="layout-grid" title="Grid">
            <svg viewBox="0 0 16 16"><rect x="0" y="0" width="7" height="7" rx="1"/><rect x="9" y="0" width="7" height="7" rx="1"/><rect x="0" y="9" width="7" height="7" rx="1"/><rect x="9" y="9" width="7" height="7" rx="1"/></svg>
          </button>
          <button class="gal-layout-btn" data-layout="layout-wide" title="Wide">
            <svg viewBox="0 0 16 16"><rect x="0" y="1" width="16" height="6" rx="1"/><rect x="0" y="9" width="16" height="6" rx="1"/></svg>
          </button>
          <button class="gal-layout-btn" data-layout="layout-strip" title="Filmstrip">
            <svg viewBox="0 0 16 16"><rect x="0" y="0" width="2" height="16" rx="1"/><rect x="3" y="0" width="2" height="16" rx="1"/><rect x="6" y="0" width="2" height="16" rx="1"/><rect x="9" y="0" width="2" height="16" rx="1"/><rect x="12" y="0" width="2" height="16" rx="1"/><rect x="15" y="0" width="1" height="16" rx="1"/></svg>
          </button>
        </div>
      </div>
    </div>

    <section class="gal-section">
      <div class="gal-wrap">
        <div class="gal-meta">
          <span class="gal-count"><strong id="galCount">{{ $galleryCount }}</strong> photos</span>
        </div>
        <div class="gal-grid layout-masonry" id="galGrid">
          @forelse ($galleryItems as $item)
            <div class="gal-item" data-cat="{{ $item['cat'] }}" data-src="{{ $item['src'] }}">
              <img loading="lazy" decoding="async" src="{{ $item['src'] }}" alt="{{ $item['alt'] }}">
              <div class="gal-item-overlay"><span class="gal-item-label">{{ $item['label'] }}</span></div>
            </div>
          @empty
            <div class="gal-empty">No gallery images found.</div>
          @endforelse
        </div>
        <div id="galPaginationBot" class="gal-pagination"></div>
      </div>
    </section>

    <section class="gal-cta">
      <div class="wrap">
        <h2>{{ $galleryContent['cta_title'] ?? 'Ready to Be Part of the Story?' }}</h2>
        <p>{{ $galleryContent['cta_intro'] ?? 'Join thousands of students building healthcare careers at GoCare Training Institute.' }}</p>
        <div class="gal-cta-btns">
          <a href="{{ route('contact') }}" class="gal-cta-btn gal-cta-btn--o">Apply Now <i data-lucide="arrow-right" style="width:16px;height:16px;vertical-align:middle;margin-left:6px"></i></a>
          <a href="{{ route('courses') }}" class="gal-cta-btn gal-cta-btn--ghost">Explore Courses</a>
        </div>
      </div>
    </section>
  </main>

  <div class="gal-lb" id="galLb" aria-hidden="true">
    <button class="gal-lb-close" id="galLbClose" aria-label="Close">&times;</button>
    <div class="gal-lb-inner">
      <img id="galLbImg" src="" alt="">
    </div>
    <button class="gal-lb-prev" id="galLbPrev" aria-label="Previous"><i data-lucide="chevron-left" style="width:22px;height:22px"></i></button>
    <button class="gal-lb-next" id="galLbNext" aria-label="Next"><i data-lucide="chevron-right" style="width:22px;height:22px"></i></button>
    <div class="gal-lb-counter" id="galLbCounter"></div>
  </div>

  <footer id="contactSection" class="footer">
    <div class="footer-pattern" aria-hidden="true"></div>
    <div class="footer-top footer-top--five">
      <div class="footer-brand">
        <a href="#" class="logo">
          <img loading="eager" decoding="async" src="{{ asset('images/gocare-institute-logo-white.png') }}" alt="GoCare Logo" style="height: 48px; width: auto; margin-bottom: 8px;">
        </a>
        <p>GoCare Training Institute offers expert-led healthcare education to shape your future.</p>
        <p class="footer-tagline"><em>Train with the Experts&hellip; Become an Expert!</em></p>
      </div>
      <div class="footer-col">
        <h4>Schools &amp; Programs</h4>
        <ul class="footer-icon-list">
          <li><a href="schools/medical-health-sciences">School of Medical &amp; Health Sciences</a></li>
          <li><a href="schools/hospitality-management">School of Hospitality Management</a></li>
          <li><a href="schools/social-sciences-business">School of Social Sciences &amp; Business Management</a></li>
          <li><a href="schools/international-certifications">International Certifications</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Resources</h4>
        <ul class="footer-icon-list">
          <li><a href="#">Student Resources</a></li>
          <li><a href="downloads">Downloads</a></li>
          <li><a href="blog">Blogs &amp; Articles</a></li>
          <li><a href="#">Testimonials &amp; Success Stories</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul class="footer-icon-list">
          <li><a href="industrial-attachment">Industrial &amp; Field Attachment</a></li>
          <li><a href="student-support-services">Student Support Services</a></li>
          <li><a href="modes-of-study">Modes of Study</a></li>
          <li><a href="apply">Apply Now</a></li>
          <li><a href="docs/GoCare%20Training%20Institute%20Prospectus.pdf" target="_blank">Download Prospectus</a></li>
          <li><a href="hostels-and-accommodation">Hostels &amp; Accommodation</a></li>
          <li><a href="#">Student Portal</a></li>
          <li><a href="#">Staff Portal</a></li>
        </ul>
      </div>
      <div class="footer-col footer-contact-col">
        <h4>Contact Us</h4>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span class="contact-detail-text">Nairobi City Campus, Nairobi CBD, GatKim Complex, Temple Road</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span class="contact-detail-text">Thika Road Campus, Eastern Bypass, Kamakis, Ruiru</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
            <span class="contact-detail-text">0703 115 502 | 0745 229 485 | 0745 220 344</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
              <polyline points="22,6 12,13 2,6" />
            </svg>
            <a href="mailto:info@gocareinstitute.ac.ke" class="contact-detail-text" style="color:inherit;text-decoration:none">info@gocareinstitute.ac.ke</a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-accred-bar">
      <p class="accred-tagline">TRAIN WITH THE EXPERTS &hellip; BECOME AN EXPERT!</p>
      <div class="accred-badges">
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/TVETA.png') }}" alt="TVETA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/tveta curriculum.png') }}" alt="TVET CDACC logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/national_industrial_training_authority_logo.png') }}" alt="NITA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/KHPOA.png') }}" alt="KHPOA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/ministry of education.png') }}" alt="Ministry of Education logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/knec.png') }}" alt="KNEC logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/amca.png') }}" alt="AMCA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/SDCC.png') }}" alt="Skill Development Council Canada logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="{{ asset('images/partners/American_Heart_Association_Logo.svg') }}" alt="American Heart Association logo"></span>
      </div>
    </div>
    <div class="footer-bottom footer-bottom--redesigned">
      <div class="footer-bottom-left">
        <div class="socials">
          <a href="https://www.facebook.com/GoCareTrainingInstitute/" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
          </a>
          <a href="https://www.instagram.com/gocaretraininginstitute/" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
          </a>
          <a href="https://x.com/GoCareInstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="X">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
          </a>
          <a href="https://www.tiktok.com/@gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="TikTok">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.08-.14 1.62.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" /></svg>
          </a>
          <a href="https://ke.linkedin.com/company/gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" /></svg>
          </a>
          <a href="https://www.youtube.com/@gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="YouTube">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
          </a>
        </div>
      </div>
      <p class="footer-copy">&copy; 2026 GoCare Training Institute. All rights reserved. | TVETA Accredited &amp; Licensed | TVET CDACC Approved | NITA Accredited | Globally Recognized</p>
    </div>
  </footer>

  <script>
    lucide.createIcons();

    var PER_PAGE = 24;
    var tabs = document.querySelectorAll('.gal-tab');
    var layoutBtns = document.querySelectorAll('.gal-layout-btn');
    var allItems = Array.from(document.querySelectorAll('.gal-item'));
    var grid = document.getElementById('galGrid');
    var countEl = document.getElementById('galCount');
    var pgBot = document.getElementById('galPaginationBot');
    var currentPage = 1;
    var currentCat = 'all';
    var currentLayout = 'layout-masonry';
    var filtered = [];
    var visibleLb = [];
    var lb = document.getElementById('galLb');
    var lbInner = lb.querySelector('.gal-lb-inner');
    var lbClose = document.getElementById('galLbClose');
    var lbPrev = document.getElementById('galLbPrev');
    var lbNext = document.getElementById('galLbNext');
    var lbCounter = document.getElementById('galLbCounter');
    var lbIdx = 0;

    allItems.forEach(function(item) {
      var img = item.querySelector('img');
      if (!img) {
        item.classList.add('loaded');
        return;
      }
      if (img.complete && img.naturalWidth > 0) {
        item.classList.add('loaded');
      } else {
        img.addEventListener('load', function() { item.classList.add('loaded'); });
        img.addEventListener('error', function() { item.classList.add('loaded'); });
      }
    });

    function getFiltered(cat) {
      return allItems.filter(function(item) {
        var cats = (item.dataset.cat || '').split(' ');
        return cat === 'all' || cats.indexOf(cat) !== -1;
      });
    }

    function totalPages() {
      return Math.max(1, Math.ceil(filtered.length / PER_PAGE));
    }

    function renderPagination(container) {
      container.innerHTML = '';
      var total = totalPages();
      if (total <= 1) {
        return;
      }

      function mkBtn(label, page, isActive, isDisabled, isEllipsis) {
        if (isEllipsis) {
          var s = document.createElement('span');
          s.className = 'gal-pg-ellipsis';
          s.textContent = '...';
          container.appendChild(s);
          return;
        }
        var b = document.createElement('button');
        b.className = 'gal-pg-btn' + (isActive ? ' active' : '');
        b.textContent = label;
        b.disabled = isDisabled;
        if (!isDisabled) {
          b.addEventListener('click', function() { renderPage(page); });
        }
        container.appendChild(b);
      }

      mkBtn('‹', currentPage - 1, false, currentPage === 1);
      var pages = [];
      for (var p = 1; p <= total; p++) {
        if (p === 1 || p === total || (p >= currentPage - 1 && p <= currentPage + 1)) pages.push(p);
      }
      var prev = 0;
      pages.forEach(function(p) {
        if (prev && p - prev > 1) mkBtn('', null, false, false, true);
        mkBtn(String(p), p, p === currentPage, false);
        prev = p;
      });
      mkBtn('›', currentPage + 1, false, currentPage === total);
    }

    function renderPage(page) {
      currentPage = page;
      var start = (page - 1) * PER_PAGE;
      var end = start + PER_PAGE;

      allItems.forEach(function(item) {
        if (item.parentNode) {
          item.parentNode.removeChild(item);
        }
      });

      visibleLb = [];
      filtered.forEach(function(item, index) {
        if (index >= start && index < end) {
          grid.appendChild(item);
          visibleLb.push(item);
        }
      });

      countEl.textContent = filtered.length;
      renderPagination(pgBot);
      window.scrollTo({ top: grid.offsetTop - 120, behavior: 'smooth' });
    }

    function applyFilter(cat) {
      currentCat = cat;
      filtered = getFiltered(cat);
      renderPage(1);
    }

    function applyLayout(layout) {
      currentLayout = layout;
      grid.classList.remove('layout-masonry', 'layout-grid', 'layout-wide', 'layout-strip');
      grid.classList.add(layout);
      layoutBtns.forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.layout === layout);
      });
    }

    tabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        tabs.forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        applyFilter(tab.dataset.cat);
      });
    });

    layoutBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        applyLayout(btn.dataset.layout);
      });
    });

    function updateCounter() {
      lbCounter.textContent = (lbIdx + 1) + ' / ' + visibleLb.length;
    }

    function setLbImage(src, alt) {
      var img = lbInner.querySelector('img');
      if (!img) {
        img = document.createElement('img');
        lbInner.appendChild(img);
      }
      img.src = src;
      img.alt = alt || '';
    }

    function openLb(index) {
      lbIdx = index;
      setLbImage(visibleLb[lbIdx].dataset.src, visibleLb[lbIdx].querySelector('img').alt);
      updateCounter();
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeLb() {
      lb.classList.remove('open');
      document.body.style.overflow = '';
    }

    function navigate(dir) {
      if (!visibleLb.length) return;
      lbIdx = (lbIdx + dir + visibleLb.length) % visibleLb.length;
      setLbImage(visibleLb[lbIdx].dataset.src, visibleLb[lbIdx].querySelector('img').alt);
      updateCounter();
    }

    allItems.forEach(function(item) {
      item.addEventListener('click', function() {
        var idx = visibleLb.indexOf(item);
        if (idx !== -1) {
          openLb(idx);
        }
      });
    });

    lbClose.addEventListener('click', closeLb);
    lb.addEventListener('click', function(e) { if (e.target === lb) closeLb(); });
    lbPrev.addEventListener('click', function() { navigate(-1); });
    lbNext.addEventListener('click', function() { navigate(1); });

    document.addEventListener('keydown', function(e) {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft') navigate(-1);
      if (e.key === 'ArrowRight') navigate(1);
    });

    applyLayout(currentLayout);
    applyFilter('all');
  </script>
</body>
</html>
