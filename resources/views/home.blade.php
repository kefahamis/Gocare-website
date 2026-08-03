<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="images/gocare-institute-logo.png">
  <link rel="apple-touch-icon" href="images/gocare-institute-logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ \App\Models\SeoSetting::current()->default_title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  @include('components.seo')
</head>

<body>

    <!-- COOKIE CONSENT -->
  <div id="cookie-banner" role="dialog" aria-label="Cookie consent" aria-live="polite">
    <div class="ck-inner">
      <div class="ck-left">
        <div class="ck-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/>
            <path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/>
          </svg>
        </div>
        <div class="ck-copy">
          <strong>We use cookies</strong>
          <p>We use cookies to improve your experience, analyse site traffic, and personalise content. By continuing, you agree to our <a href="#">Privacy Policy</a>.</p>
        </div>
      </div>
      <div class="ck-actions">
        <button class="ck-btn-decline" id="ckDecline">Decline</button>
        <button class="ck-btn-accept" id="ckAccept">Accept All</button>
        <button class="ck-close" id="ckClose" aria-label="Close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>

<!-- -- NAVBAR + SUB BAR ------------------------------- -->
  @include('partials.nav')

  <style>
    /* Hero slide bullet points & highlight banner */
    .slide-points { list-style: none; padding: 0; margin: 18px 0 24px; display: grid; gap: 11px; max-width: 560px; }
    .slide-points li { display: flex; align-items: flex-start; gap: 10px; color: rgba(255,255,255,.92); font-size: 1.02rem; line-height: 1.4; font-weight: 500; }
    .slide-points li .lucide { width: 19px; height: 19px; color: #ff9f43; flex-shrink: 0; margin-top: 2px; }
    .slide-highlight { display: inline-block; background: linear-gradient(135deg, #ec7424, #d4611a); color: #fff; font-weight: 800; font-size: 1.05rem; line-height: 1.35; padding: 13px 22px; border-radius: 10px; margin: 6px 0 20px; box-shadow: 0 10px 24px rgba(236,116,36,.4); max-width: 600px; }
    /* Entrance for slide-specific elements */
    .slide-points, .slide-highlight {
      opacity: 0; transform: translateY(40px); filter: blur(5px);
      transition: opacity .75s cubic-bezier(0.16,1,0.3,1), transform .75s cubic-bezier(0.16,1,0.3,1), filter .6s cubic-bezier(0.16,1,0.3,1);
    }
    .slide.active .slide-highlight { opacity:1; transform:none; filter:blur(0); transition-delay:.44s; }
    .slide.active .slide-points    { opacity:1; transform:none; filter:blur(0); transition-delay:.54s; }

    @media (max-width: 600px) {
      .slide-points { margin: 10px 0 14px; gap: 7px; }
      .slide-points li { font-size: .9rem; line-height: 1.3; }
      .slide-points li .lucide { width: 17px; height: 17px; }
      .slide-highlight { font-size: .9rem; padding: 9px 14px; margin: 4px 0 12px; line-height: 1.3; }
    }
  </style>

  <!-- -- HERO SLIDER ----------------------------------- -->
  <section class="hero" id="home">
    <div class="slider-wrap" id="sliderWrap">      @foreach ($sliders as $index => $slider)
        <div class="slide s{{ $index + 1 }}{{ $index === 0 ? ' active' : '' }}">
          <img loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async" class="slide-img" src="{{ $slider->image_url }}" alt="{{ $slider->alt_text ?: strip_tags($slider->title) }}">
          <div class="slide-overlay"></div>
          <div class="slide-body">
            <div class="slide-text">
              <h1>{{ $slider->title }}@if ($slider->accent_title)<br><em>{{ $slider->accent_title }}</em>@endif@if ($slider->title_suffix)<br>{{ $slider->title_suffix }}@endif</h1>
              @if ($slider->description)
                <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; line-height: 1.6; max-width: 600px; margin-bottom: 20px;">{!! $slider->description !!}</p>
              @endif
              @if ($slider->highlight)
                <div class="slide-highlight">{!! $slider->highlight !!}</div>
              @endif
              @if ($slider->points)
                <ul class="slide-points">
                  @foreach ($slider->points as $point)
                    <li><i data-lucide="check-circle-2"></i> {!! $point !!}</li>
                  @endforeach
                </ul>
              @endif
              <div class="slide-actions">
                @if ($slider->primary_label && $slider->primary_url)
                  <a href="{{ url($slider->primary_url) }}" class="btn btn-{{ $slider->primary_style }}" @if ($slider->open_new_tab) target="_blank" @endif>{{ $slider->primary_label }} <i data-lucide="arrow-right"></i></a>
                @endif
                @if ($slider->secondary_label && $slider->secondary_url)
                  <a href="{{ url($slider->secondary_url) }}" class="btn btn-{{ $slider->secondary_style }}" @if ($slider->open_new_tab) target="_blank" @endif>{{ $slider->secondary_label }}</a>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    <div class="hero-ctrl">
      <button class="arrow" id="prevSlide"><i class="fas fa-chevron-left"></i></button>
      <button class="arrow" id="nextSlide"><i class="fas fa-chevron-right"></i></button>
    </div>
    <div class="hero-dots">      @foreach ($sliders as $index => $slider)
        <div class="dot{{ $index === 0 ? ' active' : '' }}" data-i="{{ $index }}"></div>
      @endforeach
    </div>
  </section>
          
  <!-- -- WHY GOCARE PILLARS (Screenshot match) ------------- -->
  <section class="pillars-sec">
    <div class="wrap">
      <div class="pillars-header">
        <div class="pillar-line"></div>
        <h2 class="pillars-title">Why Choose GoCare?</h2>
        <div class="pillar-line"></div>
      </div>
      <div class="pillars-grid">
        <!-- Expert Trainers -->
        <div class="pillar-card">
          <div class="pillar-icon-wrapper">
            <svg class="pillar-svg-icon" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="purpleGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#8e44ad" />
                  <stop offset="100%" stop-color="#642a7e" />
                </linearGradient>
                <linearGradient id="orangeGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#ff9f43" />
                  <stop offset="100%" stop-color="#ec7424" />
                </linearGradient>
              </defs>
              <circle cx="50" cy="50" r="38" fill="url(#orangeGrad1)" opacity="0.15" />
              <path d="M50 22L18 36L50 50L82 36L50 22Z" fill="url(#purpleGrad1)" />
              <path d="M30 42.5V56C30 63 38.95 68.7 50 68.7C61.05 68.7 70 63 70 56V42.5L50 51.5L30 42.5Z" fill="url(#orangeGrad1)" />
              <path d="M78 38V58H80V38H78Z" fill="url(#purpleGrad1)" />
              <circle cx="79" cy="59" r="3" fill="url(#orangeGrad1)" />
            </svg>
          </div>
          <h3 class="pillar-card-title">Expert Trainers</h3>
          <div class="pillar-card-divider"></div>
          <p class="pillar-card-text">Learn from highly qualified, experienced industry professionals who provide hands-on practical sessions and clinical mentorship to ensure you master every skill.</p>
        </div>

        <!-- Accredited Programs -->
        <div class="pillar-card">
          <div class="pillar-icon-wrapper">
            <svg class="pillar-svg-icon" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="purpleGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#8e44ad" />
                  <stop offset="100%" stop-color="#642a7e" />
                </linearGradient>
                <linearGradient id="orangeGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#ff9f43" />
                  <stop offset="100%" stop-color="#ec7424" />
                </linearGradient>
              </defs>
              <circle cx="50" cy="50" r="38" fill="url(#purpleGrad2)" opacity="0.1" />
              <rect x="28" y="20" width="44" height="60" rx="4" fill="url(#orangeGrad2)" />
              <line x1="36" y1="32" x2="64" y2="32" stroke="white" stroke-width="3" stroke-linecap="round" />
              <line x1="36" y1="42" x2="64" y2="42" stroke="white" stroke-width="3" stroke-linecap="round" />
              <line x1="36" y1="52" x2="52" y2="52" stroke="white" stroke-width="3" stroke-linecap="round" />
              <circle cx="62" cy="62" r="14" fill="#F5F0E8" stroke="url(#purpleGrad2)" stroke-width="4" />
              <line x1="72" y1="72" x2="82" y2="82" stroke="url(#purpleGrad2)" stroke-width="5" stroke-linecap="round" />
            </svg>
          </div>
          <h3 class="pillar-card-title">Accredited Programs</h3>
          <div class="pillar-card-divider"></div>
          <p class="pillar-card-text">Study with total confidence. Our courses are fully registered, licensed, and accredited by TVETA, TVET CDACC, NITA, KHPOA, and KNEC, ensuring national and global validity.</p>
        </div>

        <!-- Career Readiness -->
        <div class="pillar-card">
          <div class="pillar-icon-wrapper">
            <svg class="pillar-svg-icon" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="purpleGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#8e44ad" />
                  <stop offset="100%" stop-color="#642a7e" />
                </linearGradient>
                <linearGradient id="orangeGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#ff9f43" />
                  <stop offset="100%" stop-color="#ec7424" />
                </linearGradient>
              </defs>
              <circle cx="50" cy="50" r="38" fill="url(#orangeGrad3)" opacity="0.15" />
              <rect x="20" y="36" width="60" height="40" rx="6" fill="url(#purpleGrad3)" />
              <path d="M38 36V28C38 25.8 39.8 24 42 24H58C60.2 24 62 25.8 62 28V36" stroke="url(#purpleGrad3)" stroke-width="4" fill="none" />
              <rect x="46" y="50" width="8" height="12" rx="2" fill="url(#orangeGrad3)" />
              <path d="M22 46V40C22 37.8 23.8 36 26 36H30" stroke="url(#orangeGrad3)" stroke-width="2" fill="none" />
              <path d="M78 46V40C78 37.8 76.2 36 74 36H70" stroke="url(#orangeGrad3)" stroke-width="2" fill="none" />
            </svg>
          </div>
          <h3 class="pillar-card-title">Career Readiness</h3>
          <div class="pillar-card-divider"></div>
          <p class="pillar-card-text">Launch your global career from day one with guaranteed hospital and field attachments, AMCA USA & SDC Canada exam centers, and high employer demand worldwide.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- -- FIND YOUR COURSE ---------------------------------- -->
  <section class="fyc-sec">
    <div class="wrap">

      <!-- Finder header -->
      <div class="fyc-header reveal">
        <div class="fyc-line"></div>
        <h2 class="fyc-title">Find Your <span class="fyc-accent">Course</span></h2>
        <div class="fyc-line"></div>
      </div>

      <!-- Filter / search bar -->
      <form class="fyc-finder reveal" id="fycFinder" action="courses" method="get">
        <div class="fyc-field">
          <label for="fycSchool">School</label>
          <div class="fyc-select-wrap">
            <select id="fycSchool" name="school">
              <option value="">Any School</option>
              <option value="schools/medical-health-sciences">Medical &amp; Health Sciences</option>
              <option value="schools/hospitality-management">Hospitality Management</option>
              <option value="schools/social-sciences-business">Social Sciences &amp; Business</option>
              <option value="schools/international-certifications">International Certifications</option>
            </select>
            <i data-lucide="chevron-down"></i>
          </div>
        </div>
        <div class="fyc-field">
          <label for="fycLevel">Program Level</label>
          <div class="fyc-select-wrap">
            <select id="fycLevel" name="level">
              <option value="">Any Level</option>
              <option value="certificate">Certificate</option>
              <option value="diploma">Diploma</option>
              <option value="certification">International Certification</option>
            </select>
            <i data-lucide="chevron-down"></i>
          </div>
        </div>
        <div class="fyc-field">
          <label for="fycDuration">Duration</label>
          <div class="fyc-select-wrap">
            <select id="fycDuration" name="duration">
              <option value="">Any Duration</option>
              <option value="6-months">6 Months</option>
              <option value="1-year">1 Year</option>
              <option value="2-years">2 Years</option>
            </select>
            <i data-lucide="chevron-down"></i>
          </div>
        </div>
        <button type="submit" class="fyc-search-btn">
          <i data-lucide="search"></i> Search
        </button>
      </form>

    </div>
  </section>

  <!-- -- FEATURED SCHOOLS & COURSES ------------------- -->
  <section class="schools-sec" id="schools">
    <div class="wrap">

      <!-- Header -->
      <div class="schools-sec-header reveal">
        <div class="schools-sec-eyebrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          4 Schools &ndash; 25+ Programmes
        </div>
        <h2 class="schools-sec-title">Explore Our <span class="hl-purple">Schools</span> &amp; <span class="hl-orange">Programmes</span></h2>
        <p class="schools-sec-sub">Four specialist schools, one world-class institution. Find the programme that launches your healthcare or professional career.</p>
      </div>

      <!-- School Tab Cards -->
      <div class="school-tabs reveal">
        <div class="school-tab-card active" id="stab-med" onclick="switchSchool('med')">
          <div class="school-tab-card-bg" style="background-image: url('images/Nursing-Assistant.jpg');"></div>
          <div class="school-tab-card-body">
            <div class="school-tab-icon">
              <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="school-tab-name">Medical &amp; Health Sciences</div>
            <div class="school-tab-count">14 Programmes</div>
            <div class="school-tab-indicator"></div>
          </div>
        </div>

        <div class="school-tab-card" id="stab-hosp" onclick="switchSchool('hosp')">
          <div class="school-tab-card-bg" style="background-image: url('images/Front-office.jpg');"></div>
          <div class="school-tab-card-body">
            <div class="school-tab-icon">
              <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="school-tab-name">Hospitality Management</div>
            <div class="school-tab-count">5 Programmes</div>
            <div class="school-tab-indicator"></div>
          </div>
        </div>

        <div class="school-tab-card" id="stab-social" onclick="switchSchool('social')">
          <div class="school-tab-card-bg" style="background-image: url('images/new-images/SOCIAL-WORK-&-COMMUNITY-DEVELOPMENT.jpeg');"></div>
          <div class="school-tab-card-body">
            <div class="school-tab-icon">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="school-tab-name">Social Sciences &amp; Business</div>
            <div class="school-tab-count">3 Programmes</div>
            <div class="school-tab-indicator"></div>
          </div>
        </div>

        <div class="school-tab-card" id="stab-intl" onclick="switchSchool('intl')">
          <div class="school-tab-card-bg" style="background-image: url('images/slider-four.jpg');"></div>
          <div class="school-tab-card-body">
            <div class="school-tab-icon">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div class="school-tab-name">International Certifications</div>
            <div class="school-tab-count">3 Programmes</div>
            <div class="school-tab-indicator"></div>
          </div>
        </div>
      </div>

      <!-- -- PANEL: Medical & Health Sciences -- -->
      <div class="school-courses-panel active" id="spanel-med">
        <div class="school-panel-header">
          <div class="school-panel-info">
            <h3><i data-lucide="stethoscope"></i> School of Medical &amp; Health Sciences</h3>
            <p>From caregiving to theatre technology &middot; our flagship school with 14 nationally accredited programmes.</p>
          </div>
          <a href="schools/medical-health-sciences" class="school-panel-cta">
            View All 14 Programmes
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
        <div class="school-featured-grid">
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/Nursing-Assistant.jpg" alt="CNA Course">
              <span class="sc-card-badge">Level 5</span>
              <span class="sc-card-dur">1&frac12; Years</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Healthcare Support Services &middot; CNA</h4>
              <p class="sc-card-desc">Become a licensed Healthcare Support Assistant / Certified Nursing Assistant delivering safe, compassionate and professional patient care.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  Examination Body: TVET CDACC<br>
                  Practice License: KHPOA 
                </div>
                <a href="courses/certificate-in-healthcare-support-services-level-5-certified-nursing-assistant" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/perioperative-level-5.jpg" alt="Theatre Technology">
              <span class="sc-card-badge">Diploma</span>
              <span class="sc-card-dur">2&frac12; Years</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Diploma in Perioperative Theatre Technology &middot; Level 6</h4>
              <p class="sc-card-desc">Master the high-stakes operating room environment with advanced clinical expertise required to thrive in specialised surgical teams.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                   Examination Body: TVET CDACC<br><p></p>
                   <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Practice License: KHPOA
                </div>
                <a href="courses/diploma-in-perioperative-theatre-technology-level-6" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/caregiving.jpg" alt="Caregiving Course">
              <span class="sc-card-badge">Diploma</span>
              <span class="sc-card-dur">8 Months</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Caregiving &middot; Level 4</h4>
              <p class="sc-card-desc">A comprehensive, competency-based programme preparing learners to become advanced healthcare assistants and professional caregivers.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  Examination Body: TVET CDACC
                </div>
                <a href="courses/certificate-in-caregiving-level-4" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- -- PANEL: Hospitality Management -- -->
      <div class="school-courses-panel" id="spanel-hosp">
        <div class="school-panel-header">
          <div class="school-panel-info">
            <h3><i data-lucide="utensils"></i> School of Hospitality Management</h3>
            <p>Professional culinary arts, front office, housekeeping, and homecare management for the modern hospitality industry.</p>
          </div>
          <a href="schools/hospitality-management" class="school-panel-cta">
            View All 5 Programmes
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
        <div class="school-featured-grid">
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/Certificate-culinary-arts.jpg" alt="Culinary Arts">
              <span class="sc-card-badge">Certificate</span>
              <span class="sc-card-dur">5 Months</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Food &amp; Beverage Production (Culinary Arts)</h4>
              <p class="sc-card-desc">A practical, competency-based programme equipping learners with foundational culinary skills for professional kitchen environments.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC
                </div>
                <a href="courses/certificate-in-food-and-beverage-production-culinary-arts-level-3" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/Front-office.jpg" alt="Front Office">
              <span class="sc-card-badge">Certificate</span>
              <span class="sc-card-dur">5 Months</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Front Office Operations &middot; Level 3</h4>
              <p class="sc-card-desc">Gain essential skills for hotel front offices, hospitality establishments, and corporate reception. Fast-track into the hospitality world.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC
                </div>
                <a href="courses/certificate-in-front-office-operations-level-3" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/Homecare-Management-4.jpg" alt="Homecare Management">
              <span class="sc-card-badge">Certificate</span>
              <span class="sc-card-dur">8 Months</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Homecare Management &middot; Level 4</h4>
              <p class="sc-card-desc">Master essential caregiving skills for a fulfilling career supporting patients in hospitals, homes, and care facilities worldwide.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC
                </div>
                <a href="courses/certificate-in-homecare-management-level-4" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- -- PANEL: Social Sciences & Business -- -->
      <div class="school-courses-panel" id="spanel-social">
        <div class="school-panel-header">
          <div class="school-panel-info">
            <h3><i data-lucide="users"></i> School of Social Sciences &amp; Business</h3>
            <p>Build a professional career in social work, office administration, and community development.</p>
          </div>
          <a href="schools/social-sciences-business" class="school-panel-cta">
            View All 3 Programmes
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
        <div class="school-featured-grid">
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/new-images/SOCIAL-WORK-&-COMMUNITY-DEVELOPMENT.jpeg" alt="Social Work">
              <span class="sc-card-badge">Diploma</span>
              <span class="sc-card-dur">2&frac12; Years</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Diploma in Social Work &amp; Community Development &middot; Level 6</h4>
              <p class="sc-card-desc">An advanced, nationally recognized qualification preparing competent, ethical, and community-focused social work professionals.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC &middot; TVETA
                </div>
                <a href="courses/diploma-in-social-work-and-community-development-level-6" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/office-administrator-level-5.png" alt="Office Administration">
              <span class="sc-card-badge">Certificate</span>
              <span class="sc-card-dur">1&frac12; Years</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Office Administrator &middot; Level 5</h4>
              <p class="sc-card-desc">Equip yourself with professional skills to manage modern office environments across corporate, government, NGO, and private sectors.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC
                </div>
                <a href="courses/certificate-in-office-administrator-level-5" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/new-images/11.jpg" alt="Community Health">
              <span class="sc-card-badge">Certificate</span>
              <span class="sc-card-dur">1&frac12; Years</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Certificate in Community Health Assistant &middot; Level 5</h4>
              <p class="sc-card-desc">Essential knowledge and practical skills for supporting community health initiatives across diverse grassroots and public health settings.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  TVET CDACC
                </div>
                <a href="courses/certificate-in-community-health-assistant-level-5" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- -- PANEL: International Certifications -- -->
      <div class="school-courses-panel" id="spanel-intl">
        <div class="school-panel-header">
          <div class="school-panel-info">
            <h3><i data-lucide="globe"></i> International Certifications Centre</h3>
            <p>Globally recognised credentials from AMCA (USA), SDC Canada, and our unique Second Course Sponsorship programme.</p>
          </div>
          <a href="schools/international-certifications" class="school-panel-cta">
            View All 3 Programmes
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
        <div class="school-featured-grid">
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/amca-usa-certification.jpg" alt="AMCA USA Certification">
              <span class="sc-card-badge">International</span>
              <span class="sc-card-dur">USA</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">AMCA &middot; American Medical Certification Association (USA)</h4>
              <p class="sc-card-desc">GoCare is an approved AMCA centre. Earn a globally recognised US credential that opens doors to international healthcare employment.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  AMCA Accredited
                </div>
                <a href="courses/amca-usa-certification" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/sdc-canada-certification.jpg" alt="SDC Canada">
              <span class="sc-card-badge">International</span>
              <span class="sc-card-dur">Canada</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">SDC &middot; Skill Development Council Canada Certification</h4>
              <p class="sc-card-desc">Canada-aligned curriculum training, examination, and certification. A globally respected credential for healthcare professionals worldwide.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  SDC Canada
                </div>
                <a href="courses/sdc-canada-certification" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
          <div class="school-course-card">
            <div class="sc-card-img">
              <img loading="lazy" decoding="async" src="images/slider-four.jpg" alt="Second Course Sponsorship">
              <span class="sc-card-badge">Second Course</span>
              <span class="sc-card-dur">Bonus</span>
            </div>
            <div class="sc-card-body">
              <h4 class="sc-card-title">Second Course Sponsorship Programme</h4>
              <p class="sc-card-desc">Eligible GoCare students receive a second professional qualification at no extra cost &middot; doubling your career prospects and global employability.</p>
              <div class="sc-card-footer">
                <div class="sc-card-accred">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  GoCare Exclusive
                </div>
                <a href="courses/second-course-sponsorship" class="sc-card-link" aria-label="View course">
                  <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom CTA strip -->
      <div class="schools-bottom-cta reveal">
        <a href="courses" class="btn-schools-all primary">
          <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          Browse All 25+ Programmes
        </a>
        <a href="apply" class="btn-schools-all secondary">
          Apply Now &rarr;
        </a>
      </div>

    </div>
  </section>

  <!-- -- HIGHLIGHTED COURSES ---------------------------- -->
  <section class="highlighted-sec" id="highlighted-courses">
    <div class="wrap">

      <!-- Header -->
      <div class="highlighted-header reveal">
        <div class="highlighted-eyebrow">
          <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Flagship Programmes
        </div>
        <h2 class="highlighted-title">Highlighted <span class="hl-o">Courses</span></h2>
        <p class="highlighted-sub">Our most sought-after programmes &middot; designed to fast-track your career in healthcare, hospitality, business, and beyond.</p>
      </div>

      <!-- 4-Card Grid -->
      <div class="highlighted-grid">

        <!-- Card 1: CNA / Healthcare Support &middot; FEATURED -->
        <div class="hl-card hl-featured reveal">
          <div class="hl-card-photo">
            <img loading="lazy" decoding="async" src="images/Nursing-Assistant.jpg" alt="Healthcare Support Services &middot; CNA">
            <div class="hl-card-photo-overlay"></div>
            <span class="hl-card-cat">Medical</span>
            <div class="hl-card-level-dur">
              <span class="hl-card-level">Certificate</span>
              <span class="hl-card-dur">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                
                1Ã‚Â½ Years
              </span>
            </div>
          </div>
          <div class="hl-card-body">
            <h3 class="hl-card-title">Certificate in Healthcare Support Services (CNA)</h3>
            <div class="hl-card-divider"></div>
            <p class="hl-card-desc">Become a licensed Certified Nursing Assistant delivering safe, compassionate, and professional patient care in hospitals and clinics.</p>
            <div class="hl-card-accred">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              TVET CDACC &middot; AMCA USA &middot; KHPOA
            </div>
            <a href="courses/certificate-in-healthcare-support-services-level-5-certified-nursing-assistant" class="hl-card-btn">
              View Details
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>

        <!-- Card 2: Perioperative Theatre Technology -->
        <div class="hl-card reveal delay-1">
          <div class="hl-card-photo">
            <img loading="lazy" decoding="async" src="images/Perioperative-Theatre-Technology.jpg" alt="Perioperative Theatre Technology">
            <div class="hl-card-photo-overlay"></div>
            <span class="hl-card-cat">Medical</span>
            <div class="hl-card-level-dur">
              <span class="hl-card-level">Diploma</span>
              <span class="hl-card-dur">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                2&frac12; Years
              </span>
            </div>
          </div>
          <div class="hl-card-body">
            <h3 class="hl-card-title">Diploma in Perioperative Theatre Technology</h3>
            <div class="hl-card-divider"></div>
            <p class="hl-card-desc">Master the high-stakes operating room environment. Gain advanced clinical skills required for specialised surgical support teams.</p>
            <div class="hl-card-accred">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              TVET CDACC &middot; TVETA &middot; KHPOA
            </div>
            <a href="courses/diploma-in-perioperative-theatre-technology-level-6" class="hl-card-btn">
              View Details
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>

        <!-- Card 3: Culinary Arts / Food & Beverage -->
        <div class="hl-card reveal delay-2">
          <div class="hl-card-photo">
            <img loading="lazy" decoding="async" src="images/Certificate-culinary-arts.jpg" alt="Culinary Arts">
            <div class="hl-card-photo-overlay"></div>
            <span class="hl-card-cat">Hospitality</span>
            <div class="hl-card-level-dur">
              <span class="hl-card-level">Certificate</span>
              <span class="hl-card-dur">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                5 Months
              </span>
            </div>
          </div>
          <div class="hl-card-body">
            <h3 class="hl-card-title">Certificate in Food &amp; Beverage Production &middot; Culinary Arts</h3>
            <div class="hl-card-divider"></div>
            <p class="hl-card-desc">A hands-on, competency-based programme that equips learners with professional culinary and kitchen management skills for the hotel industry.</p>
            <div class="hl-card-accred">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              TVET CDACC
            </div>
            <a href="courses/certificate-in-food-and-beverage-production-culinary-arts-level-3" class="hl-card-btn">
              View Details
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>

        <!-- Card 4: AMCA USA / International Certification -->
        <div class="hl-card reveal delay-2">
          <div class="hl-card-photo">
            <img loading="lazy" decoding="async" src="images/new-images/DSC00707.jpg.jpeg" alt="AMCA International Certification">
            <div class="hl-card-photo-overlay"></div>
            <span class="hl-card-cat">International</span>
            <div class="hl-card-level-dur">
              <span class="hl-card-level">Global Cert</span>
              <span class="hl-card-dur">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Flexible
              </span>
            </div>
          </div>
          <div class="hl-card-body">
            <h3 class="hl-card-title">AMCA USA &amp; SDC Canada Certification</h3>
            <div class="hl-card-divider"></div>
            <p class="hl-card-desc">Earn a globally recognised credential from AMCA (USA) or SDC (Canada). GoCare is an approved examination centre for both international bodies.</p>
            <div class="hl-card-accred">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              AMCA USA &middot; SDC Canada
            </div>
            <a href="courses/amca-usa-certification" class="hl-card-btn">
              View Details
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>

      </div><!-- /.highlighted-grid -->

      <!-- Bottom CTA -->
      <div class="highlighted-cta reveal">
        <p>Ready to start your journey? <strong>25+ accredited programmes</strong> across 4 schools are waiting for you.</p>
        <div class="hl-cta-row">
          <a href="courses" class="hl-btn-all white">
            <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Browse All Programmes
          </a>
          <a href="apply" class="hl-btn-all dark">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            Apply Now
          </a>
        </div>
      </div>

    </div>
  </section>


  <!-- -- STUDENT TESTIMONIALS & SUCCESS STORIES -------- -->
  <section class="testi-sec" id="testimonials">
    <div class="testi-hero">
      <div class="wrap">
        <div class="testi-hero-grid">
          <div class="testi-hero-content">
            <h2>Student <span class="accent">Testimonials</span><br>& Success Stories</h2>
            <p>Real Journeys. Real Success.</p>
            <a href="student-testimonials-success-stories" class="btn btn-primary" style="border-radius:50px">Read All Stories <i data-lucide="arrow-right"></i></a>
          </div>
          <div class="testi-hero-marquee">
            <div class="testi-marquee-col">
              <div class="testi-marquee-track">
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/health-nurse.jpg" alt="GoCare nursing student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/slide-three.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/slider-four.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/caregiving.jpg" alt="Student"></div>
              </div>
              <div class="testi-marquee-track" aria-hidden="true">
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/health-nurse.jpg" alt="GoCare nursing student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/slide-three.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/slider-four.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/caregiving.jpg" alt="Student"></div>
              </div>
            </div>
            <div class="testi-marquee-col reverse">
              <div class="testi-marquee-track">
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Front-office.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Nursing-Assistant.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Health-Support.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/caregiver-expert.jpg" alt="Student"></div>
              </div>
              <div class="testi-marquee-track" aria-hidden="true">
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Front-office.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Nursing-Assistant.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/Health-Support.jpg" alt="Student"></div>
                <div class="testi-marquee-img"><img loading="lazy" decoding="async" src="images/caregiver-expert.jpg" alt="Student"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="marquee-testi-wrapper wrap reveal">
          <div class="marquee-testi-grid">
            <!-- Column 1 -->
            <div class="marquee-testi-col col-1">
              <div class="marquee-testi-track duration-15">
                <!-- Testimonial 1: Klavert Daniel -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"GoCare is the place to be for y'all who seek professional healthcare training. I loved it here. Thank you to the teachers and all staff. God Bless you."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-klavert-daniel.jpg" alt="Avatar of Klavert Daniel" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Klavert Daniel</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 2: James Wambui -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The institute is very professional and organized. I acquired more than I anticipated. Your dedication, passion, and love to train are so amazing. Continue with the good and amazing work."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-james-wambui.jpg" alt="Avatar of James Wambui" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">James Wambui</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 3: Joseph Karani -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The best decision I did was to train with GoCare. They are the best training facility for the certificate and diploma medical and health courses. Best professional study, equipment, and guaranteed attachment."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-joseph-karani.jpg" alt="Avatar of Joseph Karani" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Joseph Karani</cite>
                        <span class="marquee-testi-role">Medical Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 4: Loise Wairimu -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"Training the best Caregiving and Home Care experts. I approve and recommend anyone who intends to take a course involving Caregiving and Home Care services."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-loise-wairimu.jpg" alt="Avatar of Loise Wairimu" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Loise Wairimu</cite>
                        <span class="marquee-testi-role">Caregiving Specialist</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 5: Wycliff Mugwimi -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"It was great being part of the GoCare Training Institute. I believe I'm now an expert. God bless you GoCare!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-wycliff-mugwimi.jpg" alt="Avatar of Wycliff Mugwimi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Wycliff Mugwimi</cite>
                        <span class="marquee-testi-role">Alumni</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 6: Cynthia Wambui -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"I fully recommend GoCare Training Institute 100%. Have faith, apply, join, and finish with a smile, never regret! And enjoy your fulfilling medical career thereafter."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-cynthia-wambui.jpg" alt="Avatar of Cynthia Wambui" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Cynthia Wambui</cite>
                        <span class="marquee-testi-role">Medical Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 7: Susan Kimani -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"I like the fact that the programs are all about learning how to help people, especially the people who are in need of health care. You get comprehensive skills in a short period of time."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-susan-kimani.jpg" alt="Avatar of Susan Kimani" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Susan Kimani</cite>
                        <span class="marquee-testi-role">Health Care Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 8: Judith Arogo -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"You have played a great role in my life and now I am better than before. Thank you so much GoCare."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-judith-arogo.jpg" alt="Avatar of Judith Arogo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Judith Arogo</cite>
                        <span class="marquee-testi-role">Successful Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Duplicate Column 1 for loop -->
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"GoCare is the place to be for y'all who seek professional healthcare training. I loved it here. Thank you to the teachers and all staff. God Bless you."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-klavert-daniel.jpg" alt="Avatar of Klavert Daniel" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Klavert Daniel</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The institute is very professional and organized. I acquired more than I anticipated. Your dedication, passion, and love to train are so amazing. Continue with the good and amazing work."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-james-wambui.jpg" alt="Avatar of James Wambui" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">James Wambui</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The best decision I did was to train with GoCare. They are the best training facility for the certificate and diploma medical and health courses. Best professional study, equipment, and guaranteed attachment."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-joseph-karani.jpg" alt="Avatar of Joseph Karani" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Joseph Karani</cite>
                        <span class="marquee-testi-role">Medical Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"Training the best Caregiving and Home Care experts. I approve and recommend anyone who intends to take a course involving Caregiving and Home Care services."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-loise-wairimu.jpg" alt="Avatar of Loise Wairimu" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Loise Wairimu</cite>
                        <span class="marquee-testi-role">Caregiving Specialist</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"It was great being part of the GoCare Training Institute. I believe I'm now an expert. God bless you GoCare!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-wycliff-mugwimi.jpg" alt="Avatar of Wycliff Mugwimi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Wycliff Mugwimi</cite>
                        <span class="marquee-testi-role">Alumni</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"I fully recommend GoCare Training Institute 100%. Have faith, apply, join, and finish with a smile, never regret! And enjoy your fulfilling medical career thereafter."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-cynthia-wambui.jpg" alt="Avatar of Cynthia Wambui" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Cynthia Wambui</cite>
                        <span class="marquee-testi-role">Medical Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"I like the fact that the programs are all about learning how to help people, especially the people who are in need of health care. You get comprehensive skills in a short period of time."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-susan-kimani.jpg" alt="Avatar of Susan Kimani" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Susan Kimani</cite>
                        <span class="marquee-testi-role">Health Care Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"You have played a great role in my life and now I am better than before. Thank you so much GoCare."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-judith-arogo.jpg" alt="Avatar of Judith Arogo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Judith Arogo</cite>
                        <span class="marquee-testi-role">Successful Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
              </div>
            </div>
    
            <!-- Column 2 -->
            <div class="marquee-testi-col col-2">
              <div class="marquee-testi-track duration-19">
                <!-- Testimonial 9: Joyce Gathoni -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"They gave me the best they could when I was training with them. GoCare is the place to be when you think of medical and healthcare careers training."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-joyce-gathoni.jpg" alt="Avatar of Joyce Gathoni" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Joyce Gathoni</cite>
                        <span class="marquee-testi-role">Healthcare Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 10: Peter Kamau -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"I'd recommend everyone who wishes to do a health care course to study at GoCare Training Institute. It's a very good training institution and the teachers are very friendly."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-peter-kamau.jpg" alt="Avatar of Peter Kamau" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Peter Kamau</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 11: Sir Ogada -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The training was interesting and informative. Well detailed. Friendly lectures and staff. Registered and accredited by the government. GoCare you are the Best!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-sir-ogada.jpg" alt="Avatar of Sir Ogada" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Sir Ogada</cite>
                        <span class="marquee-testi-role">Alumni</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 12: Ian Chege -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"GoCare trainers are the best. They are pioneers who have Identified the need for caregiving, there is no other comparison with them."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-ian-chege.jpg" alt="Avatar of Ian Chege" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Ian Chege</cite>
                        <span class="marquee-testi-role">Caregiving Specialist</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 13: Vellah Chebet -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"They have just what's needed for home best medical and health care Education. They also have a very friendly and willing to help fraternity."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-vellah-chebet.jpg" alt="Avatar of Vellah Chebet" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Vellah Chebet</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 14: George Odhiambo -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"This organization is really good and reliable."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-george-odhiambo.jpg" alt="Avatar of George Odhiambo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">George Odhiambo</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 15: Grace Wanjiru -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"An institution of excellence, growth, and advancement. For learners willing to expand their knowledge and soar higher in their careers, GoCare is the place to be."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-grace-wanjiru.jpg" alt="Avatar of Grace Wanjiru" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Grace Wanjiru</cite>
                        <span class="marquee-testi-role">Professional Nurse</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 16: Florence Adipo -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"Thanks for the training and the passionate tutors. I learned a lot and am ready to go take care of patients and offer community health services."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-florence-adipo.jpg" alt="Avatar of Florence Adipo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Florence Adipo</cite>
                        <span class="marquee-testi-role">Community Health Worker</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Duplicate Column 2 for loop -->
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"They gave me the best they could when I was training with them. GoCare is the place to be when you think of medical and healthcare careers training."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-joyce-gathoni.jpg" alt="Avatar of Joyce Gathoni" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Joyce Gathoni</cite>
                        <span class="marquee-testi-role">Healthcare Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"I'd recommend everyone who wishes to do a health care course to study at GoCare Training Institute. It's a very good training institution and the teachers are very friendly."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-peter-kamau.jpg" alt="Avatar of Peter Kamau" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Peter Kamau</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The training was interesting and informative. Well detailed. Friendly lectures and staff. Registered and accredited by the government. GoCare you are the Best!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-sir-ogada.jpg" alt="Avatar of Sir Ogada" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Sir Ogada</cite>
                        <span class="marquee-testi-role">Alumni</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"GoCare trainers are the best. They are pioneers who have Identified the need for caregiving, there is no other comparison with them."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-ian-chege.jpg" alt="Avatar of Ian Chege" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Ian Chege</cite>
                        <span class="marquee-testi-role">Caregiving Specialist</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"They have just what's needed for home best medical and health care Education. They also have a very friendly and willing to help fraternity."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-vellah-chebet.jpg" alt="Avatar of Vellah Chebet" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Vellah Chebet</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"This organization is really good and reliable."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-george-odhiambo.jpg" alt="Avatar of George Odhiambo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">George Odhiambo</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"An institution of excellence, growth, and advancement. For learners willing to expand their knowledge and soar higher in their careers, GoCare is the place to be."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-grace-wanjiru.jpg" alt="Avatar of Grace Wanjiru" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Grace Wanjiru</cite>
                        <span class="marquee-testi-role">Professional Nurse</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"Thanks for the training and the passionate tutors. I learned a lot and am ready to go take care of patients and offer community health services."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-florence-adipo.jpg" alt="Avatar of Florence Adipo" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Florence Adipo</cite>
                        <span class="marquee-testi-role">Community Health Worker</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
              </div>
            </div>
    
            <!-- Column 3 -->
            <div class="marquee-testi-col col-3">
              <div class="marquee-testi-track duration-17">
                <!-- Testimonial 17: Meet Veshi -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The best school for learning health-related courses. They offer the best attachment also. This is the school you are looking for!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-meet-veshi.jpg" alt="Avatar of Meet Veshi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Meet Veshi</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 18: Muthoni Njora -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"As a training institute for TVET courses, this place has been amazing. Top-notch lecturers who offer nothing but the best."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-muthoni-njora.jpg" alt="Avatar of Muthoni Njora" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Muthoni Njora</cite>
                        <span class="marquee-testi-role">TVET Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 19: Fatma Hussein -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"I was trained at GoCare. It's a good school. Thank you!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-fatma-hussein.jpg" alt="Avatar of Fatma Hussein" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Fatma Hussein</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 20: Cecilia Mwangi -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The best place to be, to grow, and nature your career. Thank you for the experience."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-cecilia-mwangi.jpg" alt="Avatar of Cecilia Mwangi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Cecilia Mwangi</cite>
                        <span class="marquee-testi-role">Career Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 21: Judith Dutira -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"Big up GoCare. A place everyone would wish to be trained."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-judith-dutira.jpg" alt="Avatar of Judith Dutira" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Judith Dutira</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 22: Maureen Wafula -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"After completing the course at GoCare and now working, I value the knowledge and skills I acquired. I am forever grateful for that decision. Long live GoCare!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-maureen-wafula.jpg" alt="Avatar of Maureen Wafula" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Maureen Wafula</cite>
                        <span class="marquee-testi-role">Working Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 23: Nancy Njoroge -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"The school fees are very affordable and fair. Compared to the quality training they offer, I confirm value for money, and I will recommend the college any time."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-nancy-njoroge.jpg" alt="Avatar of Nancy Njoroge" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Nancy Njoroge</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 24: Enock Owino -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"Beyond the quality training, I loved the excellent customer care services. Top notch!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-enock-owino.jpg" alt="Avatar of Enock Owino" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Enock Owino</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Testimonial 25: Winfred Mwende -->
                <div class="marquee-testi-card">
                  <blockquote>
                    <p>"I am grateful that the school looks for attachment placement for students in good hospitals. Thus, saving time and money for students."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-winfred-mwende.jpg" alt="Avatar of Winfred Mwende" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Winfred Mwende</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <!-- Duplicate Column 3 for loop -->
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The best school for learning health-related courses. They offer the best attachment also. This is the school you are looking for!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-meet-veshi.jpg" alt="Avatar of Meet Veshi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Meet Veshi</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"As a training institute for TVET courses, this place has been amazing. Top-notch lecturers who offer nothing but the best."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-muthoni-njora.jpg" alt="Avatar of Muthoni Njora" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Muthoni Njora</cite>
                        <span class="marquee-testi-role">TVET Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"I was trained at GoCare. It's a good school. Thank you!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-fatma-hussein.jpg" alt="Avatar of Fatma Hussein" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Fatma Hussein</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The best place to be, to grow, and nature your career. Thank you for the experience."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-cecilia-mwangi.jpg" alt="Avatar of Cecilia Mwangi" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Cecilia Mwangi</cite>
                        <span class="marquee-testi-role">Career Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"Big up GoCare. A place everyone would wish to be trained."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-judith-dutira.jpg" alt="Avatar of Judith Dutira" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Judith Dutira</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"After completing the course at GoCare and now working, I value the knowledge and skills I acquired. I am forever grateful for that decision. Long live GoCare!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-maureen-wafula.jpg" alt="Avatar of Maureen Wafula" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Maureen Wafula</cite>
                        <span class="marquee-testi-role">Working Professional</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"The school fees are very affordable and fair. Compared to the quality training they offer, I confirm value for money, and I will recommend the college any time."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-nancy-njoroge.jpg" alt="Avatar of Nancy Njoroge" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Nancy Njoroge</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"Beyond the quality training, I loved the excellent customer care services. Top notch!"</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-enock-owino.jpg" alt="Avatar of Enock Owino" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Enock Owino</cite>
                        <span class="marquee-testi-role">Student</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
                <div class="marquee-testi-card" aria-hidden="true" tabindex="-1">
                  <blockquote>
                    <p>"I am grateful that the school looks for attachment placement for students in good hospitals. Thus, saving time and money for students."</p>
                    <footer class="marquee-testi-footer">
                      <img loading="lazy" decoding="async" src="images/testimonials/grad-winfred-mwende.jpg" alt="Avatar of Winfred Mwende" class="marquee-testi-avatar">
                      <div class="marquee-testi-info">
                        <cite class="marquee-testi-name">Winfred Mwende</cite>
                        <span class="marquee-testi-role">Graduate</span>
                      </div>
                    </footer>
                  </blockquote>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      </div>
    </div>



  <!-- -- SUCCESS STORIES STATS -------------------------- -->
  <section class="success-stats-sec">
    <div class="wrap">
      <div class="success-title">
        <div class="success-eyebrow"><i data-lucide="star"></i> Graduate Outcomes</div>
        <h2>Our Success Stories</h2>
        <p class="success-title-sub">GoCare graduates are making an impact &middot; locally, internationally, and across the entire healthcare sector.</p>
      </div>

      <div class="success-grid reveal">
        <!-- Employed Locally -->
        <div class="success-col">
          <div class="success-col-num">01</div>
          <div class="success-col-head"><h4>Employed Locally</h4></div>
          <div class="success-list">
            <div class="success-item">
              <div class="success-icon"><i data-lucide="hospital"></i></div>
              <div class="success-text">Hired at Top Hospitals</div>
            </div>
            <div class="success-item">
              <div class="success-icon"><i data-lucide="award"></i></div>
              <div class="success-text">Key Roles in Healthcare</div>
            </div>
          </div>
        </div>
        <!-- Working Abroad -->
        <div class="success-col">
          <div class="success-col-num">02</div>
          <div class="success-col-head"><h4>Working Abroad</h4></div>
          <div class="success-list">
            <div class="success-item">
              <div class="success-icon"><i data-lucide="plane"></i></div>
              <div class="success-text">Got International Jobs In America, Europe, Middle East and Other Countries</div>
            </div>
            <div class="success-item">
              <div class="success-icon"><i data-lucide="globe"></i></div>
              <div class="success-text">International Careers</div>
            </div>
          </div>
        </div>
        <!-- Thriving Alumni -->
        <div class="success-col">
          <div class="success-col-num">03</div>
          <div class="success-col-head"><h4>Thriving Alumni</h4></div>
          <div class="success-list">
            <div class="success-item">
              <div class="success-icon"><i data-lucide="trending-up"></i></div>
              <div class="success-text">Successful Entrepreneurs</div>
            </div>
            <div class="success-item">
              <div class="success-icon"><i data-lucide="users"></i></div>
              <div class="success-text">Leaders in Healthcare</div>
            </div>
          </div>
        </div>
      </div>

      <div class="success-ctas">
        <a href="contact" class="btn btn-primary" style="border-radius:50px">Share Your Story <i data-lucide="arrow-right"></i></a>
        <a href="apply" class="btn btn-secondary" style="border-radius:50px">Apply Now <i data-lucide="arrow-right"></i></a>
      </div>
    </div>
  </section>


  <!-- -- NEWS ------------------------------------------- -->
  <section class="news-sec" id="news">
    <div class="wrap">

      <!-- Header row with View All link -->
      <div class="news-sec-head reveal">
        <div>
          <span class="news-sec-eyebrow"><i data-lucide="newspaper"></i> News &amp; Updates</span>
          <h2 class="news-sec-title">Latest in <span>Healthcare Training</span></h2>
        </div>
        <a href="blog" class="news-view-all">View all articles <i data-lucide="arrow-right"></i></a>
      </div>

      <!-- 3-column grid -->
      <div class="news-3col-grid">

        <a href="blog/rewarding-career-healthcare-kenya" class="nc reveal">
          <div class="nc-img">
            <img loading="lazy" decoding="async" src="images/new-images/DSC07775.jpg" alt="Inside GoCare">
            <span class="nc-badge nc-badge--insight"><i class="fa-solid fa-lightbulb"></i> Insight</span>
          </div>
          <div class="nc-body">
            <p class="nc-date"><i class="fa-regular fa-calendar"></i> March 5, 2026</p>
            <h3>Launching a Rewarding Career in Kenya's Healthcare Sector</h3>
            <p class="nc-excerpt">Discover how GoCare graduates are transforming Kenya's healthcare sector &middot; from caregiving to theatre technology and beyond.</p>
            <span class="nc-read">Read Story <i data-lucide="arrow-right"></i></span>
          </div>
        </a>

        <a href="blog/short-courses-quick-employment" class="nc reveal delay-1">
          <div class="nc-img">
            <img loading="lazy" decoding="async" src="images/new-images/WhatsApp%20Image%202026-05-05%20at%2015.49.30.jpeg" alt="Best Marketable Courses 2026">
            <span class="nc-badge nc-badge--career"><i class="fa-solid fa-chart-line"></i> Career</span>
          </div>
          <div class="nc-body">
            <p class="nc-date"><i class="fa-regular fa-calendar"></i> February 20, 2026</p>
            <h3>Best Short Courses for Quick Employment in Kenya</h3>
            <p class="nc-excerpt">Stay ahead of the job market with these in-demand healthcare and hospitality programmes available at GoCare.</p>
            <span class="nc-read">Discover Path <i data-lucide="arrow-right"></i></span>
          </div>
        </a>

        <a href="blog/how-to-become-cna-kenya" class="nc reveal delay-2">
          <div class="nc-img">
            <img loading="lazy" decoding="async" src="images/new-images/IMG_7841.JPG" alt="Become a Certified Caregiver">
            <span class="nc-badge nc-badge--guide"><i class="fa-solid fa-compass"></i> Guide</span>
          </div>
          <div class="nc-body">
            <p class="nc-date"><i class="fa-regular fa-calendar"></i> January 12, 2026</p>
            <h3>How to Become a Certified Nursing Assistant (CNA) in Kenya</h3>
            <p class="nc-excerpt">A step-by-step guide to earning your caregiving certification and launching a rewarding healthcare career.</p>
            <span class="nc-read">Explore Now <i data-lucide="arrow-right"></i></span>
          </div>
        </a>

      </div>
    </div>
  </section>


  <!-- -- ACCREDITATION ---------------------------------- -->
  <section class="accred-section">
    <div class="accred-label reveal">Registered, Accredited, Licensed, Approved &amp; Recognised By</div>
    <div class="logo-slider">
      <div class="logo-track">
        <!-- Original set -->
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/TVETA.png" alt="TVETA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/tveta curriculum.png" alt="TVET CDACC"></div>
        <div class="accred-pill"><img loading="eager" decoding="async" src="images/partners/national_industrial_training_authority_logo.png" alt="NITA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/KHPOA.png" alt="KHPOA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/ministry of education.png" alt="Ministry of Education"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/knec.png" alt="KNEC"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/amca.png" alt="AMCA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/amca exams.png" alt="AMCA Exams"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/SDCC.png" alt="SDC Canada"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/ICDL.png" alt="ICDL"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/KATTI.png" alt="KATTI"></div>
        <div class="accred-pill"><img loading="eager" decoding="async" src="images/partners/American_Heart_Association_Logo.svg" alt="American Heart Association"></div>
        <!-- Duplicate set for infinite loop -->
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/TVETA.png" alt="TVETA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/tveta curriculum.png" alt="TVET CDACC"></div>
        <div class="accred-pill"><img loading="eager" decoding="async" src="images/partners/national_industrial_training_authority_logo.png" alt="NITA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/KHPOA.png" alt="KHPOA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/ministry of education.png" alt="Ministry of Education"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/knec.png" alt="KNEC"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/amca.png" alt="AMCA"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/amca exams.png" alt="AMCA Exams"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/SDCC.png" alt="SDC Canada"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/ICDL.png" alt="ICDL"></div>
        <div class="accred-pill"><img loading="lazy" decoding="async" src="images/partners/KATTI.png" alt="KATTI"></div>
        <div class="accred-pill"><img loading="eager" decoding="async" src="images/partners/American_Heart_Association_Logo.svg" alt="American Heart Association"></div>
      </div>
    </div>
  </section>

  <!-- -- NEWSLETTER ------------------------------------- -->
  <section class="nl-sec">
    <div class="wrap nl-inner">
      <span class="nl-eyebrow"><i data-lucide="bell"></i> Don't Miss Out</span>
      <h2>Your Career Starts with One Email</h2>
      <p>Be the first to know about new intakes, scholarship opportunities and career tips that could change your future.</p>
      <form class="nl-form"
        onsubmit="event.preventDefault();this.innerHTML='<p style=\'color:var(--dark);font-weight:700;padding:16px 24px;font-size:.95rem\'>? Subscribed! Thank you.</p>'">
        <input type="email" placeholder="Enter your email address&ndash;" required>
        <button type="submit">Join 2,500+ Students</button>
      </form>
      <p class="nl-note"><i data-lucide="lock"></i> No spam, ever. Unsubscribe at any time.</p>
    </div>
  </section>

  @include('partials.footer')

  <script>
    /* -- COOKIE -------------------- */
    const ck = document.getElementById('cookie');
    try { if (document.cookie.includes('gc_ck=')) ck.style.display = 'none'; } catch (e) { }
    document.getElementById('ckAccept').onclick = () => { try { document.cookie = 'gc_ck=1;path=/;max-age=31536000'; } catch (e) { } ck.style.display = 'none' };
    document.getElementById('ckDecline').onclick = () => ck.style.display = 'none';

    /* -- NAVBAR SCROLL SHADOW ------ */
    window.addEventListener('scroll', () => {
      document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
    });

    /* -- MOBILE MENU --------------- */
    const ham = document.getElementById('hamBtn'), mob = document.getElementById('mobMenu');
    const mobOvl = document.createElement('div'); mobOvl.className = 'mob-overlay'; document.body.appendChild(mobOvl);
    const _logoSrc = document.querySelector('.logo img')?.src || 'images/gocare-institute-logo.png';
    const mobDrawHead = document.createElement('div'); mobDrawHead.className = 'mob-drawer-head'; mobDrawHead.innerHTML = '<img loading="eager" decoding="async" src="' + _logoSrc + '" alt="GoCare"><button class="mob-drawer-close" id="mobClose"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>'; document.body.appendChild(mob); mob.prepend(mobDrawHead);
    function openDrawer(){ ham.classList.add('open'); mobOvl.style.display='block'; mob.animate([{transform:'translateX(105vw)'},{transform:'translateX(0)'}],{duration:450,easing:'cubic-bezier(.76,0,.24,1)',fill:'forwards'}); mobOvl.animate([{opacity:0},{opacity:1}],{duration:280,fill:'forwards'}); document.body.style.overflow='hidden'; }
    function closeDrawer(){ ham.classList.remove('open'); document.body.style.overflow=''; mob.animate([{transform:'translateX(0)'},{transform:'translateX(105vw)'}],{duration:450,easing:'cubic-bezier(.76,0,.24,1)',fill:'forwards'}); mobOvl.animate([{opacity:1},{opacity:0}],{duration:280,fill:'forwards'}).onfinish=()=>{mobOvl.style.display='none';}; }
    ham.addEventListener('click', () => ham.classList.contains('open') ? closeDrawer() : openDrawer());
    mob.querySelectorAll('a').forEach(a => a.addEventListener('click', closeDrawer));
    mobOvl.addEventListener('click', closeDrawer);
    document.getElementById('mobClose').addEventListener('click', closeDrawer);

    /* -- HERO SLIDER &middot; Curtain Wipe --------------- */
    (function() {
      const slides  = document.querySelectorAll('.slide');
      const dots    = document.querySelectorAll('.dot');
      const wrap    = document.getElementById('sliderWrap');
      const hero    = document.querySelector('.hero');
      if (!slides.length || !wrap) return;

      const SLIDE_DUR   = 6200;
      const EASE_OUT    = 'cubic-bezier(0.16, 1, 0.3, 1)';

      const curEl   = document.getElementById('slideCur');
      const progBar = document.getElementById('heroProgBar');
      const N       = slides.length;
      let cur = 0, busy = false, timer;
      let touchX = 0;
      const TRANS = 1050; /* push duration ms */
      const EASE  = 'cubic-bezier(0.76, 0, 0.24, 1)'; /* fast-in, crisp-stop */

      function restartProg() {
        if (!progBar) return;
        progBar.style.animation = 'none';
        void progBar.offsetWidth;
        progBar.style.animation = `progfill ${SLIDE_DUR}ms linear forwards`;
      }

      function flipCounter(val) {
        if (!curEl) return;
        curEl.style.transition = 'none';
        curEl.style.opacity = '0';
        curEl.style.transform = 'translateY(-12px)';
        requestAnimationFrame(() => {
          curEl.textContent = String(val + 1).padStart(2, '0');
          requestAnimationFrame(() => {
            curEl.style.transition = `opacity 0.4s ${EASE_OUT}, transform 0.4s ${EASE_OUT}`;
            curEl.style.opacity = '1';
            curEl.style.transform = 'translateY(0)';
          });
        });
      }

      function goTo(n, dir) {
        if (busy) return;
        const next = (n + N) % N;
        if (next === cur) return;
        busy = true;

        if (dir === undefined) {
          const fwd = (next - cur + N) % N;
          const bwd = (cur - next + N) % N;
          dir = fwd <= bwd ? 1 : -1;
        }

        const outgoing = slides[cur];
        const incoming = slides[next];
        const inImg    = incoming.querySelector('.slide-img');
        const outImg   = outgoing.querySelector('.slide-img');

        /* Keep outgoing visible underneath while incoming slides over it */
        outgoing.classList.add('slide-out');   /* opacity:1, z-index:2          */
        outgoing.classList.remove('active');   /* removes z-index:3, keeps z:2  */
        if (dots[cur]) dots[cur].classList.remove('active');

        /* Snap incoming off-screen &middot; it will slide OVER the outgoing */
        incoming.style.transition = 'none';
        incoming.style.transform  = `translateX(${dir * 100}%)`;
        incoming.style.zIndex     = '4';       /* on top of outgoing             */

        /* Image starts slightly offset for parallax depth */
        if (inImg)  { inImg.style.transition = 'none'; inImg.style.transform = `translateX(${dir * -14}%)`; }

        /* Force reflow */
        incoming.getBoundingClientRect();

        /* Slide incoming in &middot; covers the outgoing completely, no gaps */
        incoming.style.transition = `transform ${TRANS}ms ${EASE}`;
        incoming.style.transform  = 'translateX(0)';
        if (inImg) { inImg.style.transition = `transform ${TRANS}ms ${EASE}`; inImg.style.transform = 'translateX(0)'; }

        /* Outgoing recedes: scale down + fade, stays centred &middot; no horizontal gap */
        outgoing.style.transition = `transform ${TRANS}ms ${EASE}, opacity ${Math.round(TRANS * 0.65)}ms ${EASE}`;
        outgoing.style.transform  = 'scale(0.93)';
        outgoing.style.opacity    = '0';
        if (outImg) { outImg.style.transition = `transform ${TRANS}ms ${EASE}`; outImg.style.transform = `translateX(${dir * 6}%)`; }

        /* Activate incoming immediately &middot; triggers Ken Burns + text stagger */
        cur = next;
        incoming.classList.add('active');
        if (dots[cur]) dots[cur].classList.add('active');
        flipCounter(cur);
        restartProg();

        setTimeout(() => {
          outgoing.classList.remove('slide-out');
          outgoing.style.cssText = '';
          if (outImg) outImg.style.cssText = '';
          incoming.style.transition = '';
          incoming.style.transform  = '';
          incoming.style.zIndex     = '';
          if (inImg)  inImg.style.cssText = '';
          busy = false;
        }, TRANS + 80);
      }

      /* Auto-play */
      const startAuto = () => { timer = setInterval(() => { if (!busy) goTo(cur + 1, 1); }, SLIDE_DUR); };
      const resetAuto = () => { clearInterval(timer); startAuto(); };

      /* Arrow buttons */
      const nextBtn = document.getElementById('nextSlide');
      const prevBtn = document.getElementById('prevSlide');
      if (nextBtn) nextBtn.onclick = () => { goTo(cur + 1,  1); resetAuto(); };
      if (prevBtn) prevBtn.onclick = () => { goTo(cur - 1, -1); resetAuto(); };

      /* Dot navigation */
      dots.forEach(d => d.onclick = () => { goTo(+d.dataset.i); resetAuto(); });

      /* Keyboard navigation */
      document.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') { goTo(cur + 1,  1); resetAuto(); }
        if (e.key === 'ArrowLeft')  { goTo(cur - 1, -1); resetAuto(); }
      });

      /* Touch / swipe */
      wrap.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
      wrap.addEventListener('touchend',   e => {
        const diff = touchX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { const d = diff > 0 ? 1 : -1; goTo(cur + d, d); resetAuto(); }
      });

      /* Init */
      restartProg();
      startAuto();
    })();

    /* -- COUNTER ------------------- */
    let counted = false;
    function runCounters() {
      document.querySelectorAll('[data-target]').forEach(el => {
        const t = +el.dataset.target, dur = 1800, step = t / (dur / 16);
        let v = 0;
        const iv = setInterval(() => {
          v += step; if (v >= t) { v = t; clearInterval(iv) }
          el.textContent = Math.floor(v).toLocaleString() + (t >= 10 ? '+' : '');
        }, 16);
      });
    }


    /* -- COURSES ------------------- */
    const courses = [
      // Medical & Health Sciences
      { title: "Certificate in Healthcare Support Services &middot; Level 5 (CNA)", icon: "heart-handshake", level: "Level 5", dur: "1&frac12; Years", school: "med", img: "images/Nursing-Assistant.jpg", link: "courses/certificate-in-healthcare-support-services-level-5-certified-nursing-assistant", desc: "A comprehensive, competency-based program preparing learners to become licensed Healthcare Support Assistants / Certified Nursing Assistants (CNA) capable of delivering safe, compassionate, and professional patient care." },
      { title: "Certificate in Caregiving &middot; Level 4", icon: "heart-pulse", level: "Level 4", dur: "8 Months", school: "med", img: "images/caregiving.jpg", link: "courses/certificate-in-caregiving-level-4", desc: "A comprehensive, competency-based program preparing learners to become advanced healthcare assistants and professional caregivers &middot; delivering safe, competent, and compassionate care." },
      { title: "Caregiver Course &middot; Level II", icon: "stethoscope", level: "Level 2", dur: "6 Months", school: "med", img: "images/caregiver-level-2.jpg", link: "courses/caregiver-course-level-ii", desc: "A comprehensive, NITA-accredited program preparing learners to become professional caregivers and patient attendants &middot; delivering safe, compassionate, and quality care in clinical and community settings." },
      { title: "Certificate in HomeBased Care Support &middot; Level 3", icon: "heart", level: "Level 3", dur: "6 Months", school: "med", img: "images/homecare-level-3.jpg", link: "courses/certificate-in-home-based-care-support-level-3", desc: "A foundational program preparing learners to become Home-Based Care Assistants, Health Care Assistants (HCA), and Caregivers capable of delivering compassionate care." },
      { title: "Certificate in Health Care Assistant (HCA)", icon: "heart-handshake", level: "Certificate", dur: "1 Year", school: "med", img: "images/Healthcare-professional.jpg", link: "courses/certificate-in-health-care-assistant-hca", desc: "A foundational program preparing learners to become Health Care Assistants, Home-Based Care Support Assistants, and Caregivers capable of delivering safe, competent, and compassionate care." },
      { title: "Certified Nursing Assistant (CNA) Options", icon: "user-plus", level: "Certification", dur: "Varies", school: "med", img: "images/cna-options.jpg", link: "courses/certified-nursing-assistant-cna-options", desc: "Internationally recognized training and certification through two globally respected healthcare accreditation bodies: SDC Canada and AMCA USA." },
      { title: "Diploma in Perioperative Theatre Technology &middot; Level 6", icon: "activity", level: "Diploma", dur: "2&frac12; Years", school: "med", img: "images/Perioperative-Theatre-Technology.jpg", link: "courses/diploma-in-perioperative-theatre-technology-level-6", desc: "Master the high-stakes environment of the operating room. This advanced program equips you with the in-depth clinical expertise required to thrive in surgical teams." },
      { title: "Certificate in Perioperative Theatre Technology &middot; Level 5", icon: "activity", level: "Certificate", dur: "1 Year", school: "med", img: "images/perioperative-level-5.jpg", link: "courses/certificate-in-perioperative-theatre-technology-level-5", desc: "A foundational, competency-based program preparing learners to support surgical teams within operating theatre environments with essential skills in theatre management." },
      { title: "Diploma in Mortuary Science &middot; Level 6", icon: "skull", level: "Diploma", dur: "2 Years", school: "med", img: "images/Mortuary.jpg", link: "courses/diploma-in-mortuary-science-level-6", desc: "An advanced, competency-based program preparing learners for professional practice in mortuary operations, embalming, forensic support, post-mortem procedures and funeral services." },
      { title: "Certificate in Mortuary Science &middot; Level 5", icon: "skull", level: "Certificate", dur: "1 Year", school: "med", img: "images/mortuary-level-5.png", link: "courses/certificate-in-mortuary-science-level-5", desc: "A foundational, competency-based program preparing learners for professional roles within mortuaries, funeral homes, hospitals, and forensic environments." },
      { title: "Diploma in Orthopaedic & Trauma Medicine &middot; Level 6", icon: "bone", level: "Diploma", dur: "2 Years", school: "med", img: "images/orthopaedic-level-6.png", link: "courses/diploma-in-orthopaedic-and-trauma-medicine-level-6", desc: "An advanced, competency-based program preparing learners to provide professional orthopaedic and trauma care in hospitals, emergency units, and specialized orthopaedic clinics." },
      { title: "Certificate in Orthopaedic & Trauma Medicine &middot; Level 5", icon: "bone", level: "Certificate", dur: "1 Year", school: "med", img: "images/Orthopaedic-Trauma Medicine.jpg", link: "courses/certificate-in-orthopaedic-and-trauma-medicine-level-5", desc: "A specialized, competency-based program equipping learners with foundational skills in the management of musculoskeletal injuries, trauma care, emergency response, and orthopaedic support." },
      { title: "Diploma In Community Health Assistant &middot; Level 6", icon: "users", level: "Diploma", dur: "2 Years", school: "med", img: "images/Diploma-Health.jpg", link: "courses/diploma-in-community-health-assistant-level-6", desc: "An advanced, competency-based program preparing learners for professional roles in community health, public health promotion, disease prevention, and community-based healthcare." },
      { title: "Certificate In Community Health Assistant &middot; Level 5", icon: "users", level: "Certificate", dur: "1&frac12; Years", school: "med", img: "images/certificate-community-health.jpg", link: "courses/certificate-in-community-health-assistant-level-5", desc: "A foundational program equipping learners with essential knowledge and practical skills for supporting community health initiatives across diverse settings." },

      // Hospitality Management
      { title: "Certificate In Food & Beverage Production (Culinary Arts) &ndash; Level 3", icon: "utensils", level: "Level 3", dur: "5 Months", school: "hosp", img: "images/Certificate-culinary-arts.jpg", link: "courses/certificate-in-food-and-beverage-production-culinary-arts-level-3", desc: "A practical, competency-based program designed to equip learners with foundational culinary skills for professional kitchen environments." },
      { title: "Certificate In Front Office Operations &middot; Level 3", icon: "bell", level: "Level 3", dur: "5 Months", school: "hosp", img: "images/Front-office.jpg", link: "courses/certificate-in-front-office-operations-level-3", desc: "A foundational program equipping learners with the essential skills required to work professionally in hotel front offices, hospitality establishments, and corporate reception." },
      { title: "Certificate In Housekeeping & Accommodation &middot; Level 3", icon: "home", level: "Level 3", dur: "6 Months", school: "hosp", img: "images/Housekeeping-Accomodation.jpg", link: "courses/certificate-in-housekeeping-and-accommodation-level-3", desc: "A foundational, competency-based program equipping learners with essential skills to deliver professional housekeeping services in hotels, resorts, corporate and residential environments." },
      { title: "Certificate In Homecare Management &middot; Level 4", icon: "home", level: "Level 4", dur: "8 Months", school: "hosp", img: "images/Homecare-Management-4.jpg", link: "courses/certificate-in-homecare-management-level-4", desc: "Master the essential skills of compassionate caregiving. This comprehensive programme prepares you for a fulfilling career supporting patients in hospitals, homes, and care facilities." },
      { title: "Certificate In Homecare Management &middot; Level 3", icon: "home", level: "Level 3", dur: "6 Months", school: "hosp", img: "images/Homecare-Management.jpg", link: "courses/certificate-in-homecare-management-level-3", desc: "Master the essential skills of compassionate caregiving. This comprehensive programme prepares you for a fulfilling career supporting patients in hospitals, homes, and care facilities." },

      // Social Sciences
      { title: "Diploma in Social Work & Community Development &middot; Level 6", icon: "users", level: "Diploma", dur: "2&frac12; Years", school: "social", img: "images/Social Work-Community-Development.jpg", link: "courses/diploma-in-social-work-and-community-development-level-6", desc: "An advanced, nationally recognized qualification designed to prepare learners to become competent, ethical, and community-focused social work professionals." },
      { title: "Certificate In Office Administrator &middot; Level 5", icon: "briefcase", level: "Certificate", dur: "1&frac12; Years", school: "social", img: "images/office-administrator-level-5.png", link: "courses/certificate-in-office-administrator-level-5", desc: "A practical, competency-based program equipping learners with professional skills to manage modern office environments across corporate, government, NGO, and private sectors." },
      { title: "Certificate In Office Assistant / Customer Service &middot; Level 4", icon: "user-check", level: "Certificate", dur: "6 Months", school: "social", img: "images/Certificate-culinary-arts.jpg", link: "courses/certificate-in-office-assistant-customer-service-level-4", desc: "A foundational, competency-based program designed to equip learners with essential administrative, clerical, and customer service skills required in modern office environments." },

      // International
      { title: "The American Medical Certification Association (AMCA) &ndash; (USA) Certification", icon: "globe", level: "International", dur: "Varies", school: "intl", img: "images/amca-usa-certification.jpg", link: "courses/amca-usa-certification", desc: "Unlock global career opportunities with AMCA certification. As an approved center, GoCare provides students with the training and examination environment to earn this globally recognized credential." },
      { title: "Skill Development Council (SDC) &ndash; Canada Certification", icon: "globe", level: "International", dur: "Varies", school: "intl", img: "images/sdc-canada-certification.jpg", link: "courses/sdc-canada-certification", desc: "GoCare Training Institute offers Canada-aligned curriculum training, examination, and certification through the Skill Development Council (SDC) &ndash; Canada, a globally respected credential." },
      { title: "Second Course Sponsorship", icon: "gift", level: "Offer", dur: "Free", school: "intl", img: "images/slider-four.jpg", link: "courses/second-course-sponsorship", desc: "At GoCare Training Institute, we are committed to maximizing your value. Through our Second Course Sponsorship program, eligible students can receive a second professional qualification at no extra cost." }
    ];
    let vis = 6, filtered = courses, activeCat = 'all';

    function renderC() {
      const g = document.getElementById('cgrid');
      if (!g) return;
      g.innerHTML = filtered.slice(0, vis).map(c => `
    <div class="course-card reveal">
      <div class="card-img">
        <img loading="lazy" decoding="async" src="${c.img}" alt="${c.title}">
        <div class="card-level">${c.level}</div>
        <div class="card-school-badge">${c.school === 'med' ? 'Medical' : c.school === 'social' ? 'Social Sciences' : c.school === 'hosp' ? 'Hospitality' : 'International'}</div>
      </div>
      <div class="card-content">
        <div class="card-icon"><i data-lucide="${c.icon}"></i></div>
        <h3>${c.title}</h3>
        <p class="card-desc">${c.desc}</p>
        <div class="card-footer">
          <div class="card-meta">
            <span><i data-lucide="clock"></i>${c.dur}</span>
            <span><i data-lucide="award"></i>CDACC/NITA</span>
          </div>
          <a href="${c.link || 'apply'}" class="card-arrow"><i data-lucide="arrow-right"></i></a>
        </div>
      </div>
    </div>`).join('');
      const lwrap = document.getElementById('lwrap');
      if (lwrap) lwrap.style.display = vis >= filtered.length ? 'none' : 'block';
      if (window.lucide) lucide.createIcons();
      initReveal();
    }

    function filterC() {
      const q = document.getElementById('csearch') ? document.getElementById('csearch').value.toLowerCase() : '';
      filtered = courses.filter(c => {
        const matchesSearch = c.title.toLowerCase().includes(q) || c.level.toLowerCase().includes(q);
        const matchesCat = activeCat === 'all' || c.school === activeCat;
        return matchesSearch && matchesCat;
      });
      vis = 6; renderC();
    }

    function setCat(cat, el) {
      activeCat = cat;
      document.querySelectorAll('.cat-btn').forEach(btn => btn.classList.remove('active'));
      el.classList.add('active');
      filterC();
    }
    
    const csearch = document.getElementById('csearch');
    if (csearch) {
      csearch.addEventListener('input', filterC);
      csearch.addEventListener('keydown', e => { if (e.key === 'Enter') filterC() });
    }
    const loadMore = document.getElementById('loadMore');
    if (loadMore) {
      loadMore.onclick = () => { vis = Math.min(vis + 3, filtered.length); renderC(); setTimeout(() => window.scrollBy({ top: 200, behavior: 'smooth' }), 100) };
    }
    
    if (document.getElementById('cgrid')) {
      renderC();
    }

    /* -- TESTIMONIALS SLIDER --------- */
    const tGrid = document.getElementById('testi-grid');
    const tCards = document.querySelectorAll('.testi-card');
    const tDots = document.getElementById('testi-dots');
    const prev = document.getElementById('prev-btn');
    const next = document.getElementById('next-btn');
    let tIdx = 0;

    if (tGrid && tCards.length > 0) {
      // Clear existing dots first to avoid duplicates
      tDots.innerHTML = '';
      tCards.forEach((_, i) => {
        const dot = document.createElement('div');
        dot.className = 'testi-dot' + (i === 0 ? ' active' : '');
        dot.onclick = () => showSlide(i);
        tDots.appendChild(dot);
      });

      function updateSlider() {
        const cardWidth = tCards[0].offsetWidth + 30; // card + gap (match CSS)
        tGrid.style.transform = `translateX(-${tIdx * cardWidth}px)`;
        document.querySelectorAll('.testi-dot').forEach((d, i) => d.classList.toggle('active', i === tIdx));
      }

      function showSlide(idx) {
        tIdx = (idx + tCards.length) % tCards.length;
        updateSlider();
      }

      if(next) next.onclick = () => showSlide(tIdx + 1);
      if(prev) prev.onclick = () => showSlide(tIdx - 1);
      
      window.addEventListener('resize', updateSlider);
      // Auto-slide every 8s
      setInterval(() => showSlide(tIdx + 1), 8000);
    }

    /* -- REVEAL + COUNTERS --------- */
    function initReveal() {
      const io = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('up'); io.unobserve(e.target) } });
      }, { threshold: .1 });
      document.querySelectorAll('.reveal:not(.up),.reveal-left:not(.up),.reveal-right:not(.up)').forEach(el => io.observe(el));

      if (!counted) {
        const strip = document.querySelector('.stats-strip');
        if (strip) {
          const co = new IntersectionObserver(en => {
            if (en[0].isIntersecting) { counted = true; runCounters(); co.disconnect() }
          }, { threshold: .3 });
          co.observe(strip);
        }
      }
    }
    initReveal();
    lucide.createIcons();

    /* -- SCHOOL TABS --------- */
    function switchSchool(key) {
      // Update tab cards
      document.querySelectorAll('.school-tab-card').forEach(function(card) {
        card.classList.remove('active');
      });
      var activeTab = document.getElementById('stab-' + key);
      if (activeTab) activeTab.classList.add('active');

      // Update panels
      document.querySelectorAll('.school-courses-panel').forEach(function(panel) {
        panel.classList.remove('active');
      });
      var activePanel = document.getElementById('spanel-' + key);
      if (activePanel) activePanel.classList.add('active');
    }

    /* -- FIND YOUR COURSE finder --------- */
    (function () {
      var finder = document.getElementById('fycFinder');
      if (!finder) return;
      finder.addEventListener('submit', function (e) {
        e.preventDefault();
        var school = document.getElementById('fycSchool').value;
        var level = document.getElementById('fycLevel').value;
        var duration = document.getElementById('fycDuration').value;
        var params = [];
        if (level) params.push('level=' + encodeURIComponent(level));
        if (duration) params.push('duration=' + encodeURIComponent(duration));
        // Route to the chosen school page when picked, otherwise the full course list
        var base = school || 'courses';
        window.location.href = params.length ? base + '?' + params.join('&') : base;
      });
    })();
  </script>
  <script src="script.js"></script>
  <script>
    (function() {
      var banner = document.getElementById('cookie-banner');
      if (!banner) return;
      if (localStorage.getItem('ck_consent')) return;
      setTimeout(function() { banner.classList.add('ck-visible'); }, 1000);
      function dismiss() {
        banner.classList.remove('ck-visible');
        banner.classList.add('ck-hiding');
      }
      var accept = document.getElementById('ckAccept');
      var decline = document.getElementById('ckDecline');
      var close = document.getElementById('ckClose');
      if (accept) accept.addEventListener('click', function() { localStorage.setItem('ck_consent', 'accepted'); dismiss(); });
      if (decline) decline.addEventListener('click', function() { localStorage.setItem('ck_consent', 'declined'); dismiss(); });
      if (close) close.addEventListener('click', dismiss);
    })();
  </script>

  <!-- BACK TO TOP -->
  <button class="back-to-top" id="backToTop" aria-label="Back to top">
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
  </button>
  <script>
    (function(){
      var btn = document.getElementById('backToTop');
      if(!btn) return;
      window.addEventListener('scroll', function(){
        btn.classList.toggle('visible', window.scrollY > 400);
      });
      btn.addEventListener('click', function(){
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    })();

    /* -- HOME CTA SCRAMBLE BUTTON -- */
    (function(){
      const hmScrambleBtn = document.getElementById('hmScrambleBtn');
      if (!hmScrambleBtn) return;
      const originalText = "Apply Now";
      const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
      let isScrambling = false;

      hmScrambleBtn.addEventListener('mouseenter', () => {
        if (isScrambling) return;
        isScrambling = true;
        let iteration = 0;
        const maxIterations = originalText.length;

        const interval = setInterval(() => {
          hmScrambleBtn.textContent = originalText
            .split("")
            .map((letter, index) => {
              if (index < iteration) {
                return originalText[index];
              }
              return chars[Math.floor(Math.random() * chars.length)];
            })
            .join("");

          if (iteration >= maxIterations) {
            clearInterval(interval);
            hmScrambleBtn.textContent = originalText;
            isScrambling = false;
          }

          iteration += 1 / 3;
        }, 30);
      });
      
      hmScrambleBtn.addEventListener('click', () => {
        window.location.href = 'apply';
      });
    })();
  </script>
  <script src="js/stagger_testi.js"></script>
  <script src="mobile-nav.js"></script>
  <script src="search-index.js" defer></script>
  <script id="gc-search-js" src="search.js" defer></script>
  <script src="accessibility.js" defer></script>
</body>

</html>






