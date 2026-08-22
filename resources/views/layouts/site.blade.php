<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="/images/gocare-institute-logo.png">
  <link rel="apple-touch-icon" href="/images/gocare-institute-logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $metaTitle ?? \App\Models\SeoSetting::current()->default_title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
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

  <!-- NAVBAR + SUB BAR -->
  @include('partials.nav')

  <main>
    @yield('content')
  </main>

  @include('partials.footer')

  <script>
    /* -- NAVBAR SCROLL SHADOW ------ */
    window.addEventListener('scroll', () => {
      document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
    });

    /* -- MOBILE MENU --------------- */
    const ham = document.getElementById('hamBtn'), mob = document.getElementById('mobMenu');
    const mobOvl = document.createElement('div'); mobOvl.className = 'mob-overlay'; document.body.appendChild(mobOvl);
    var _logoEl = document.querySelector('.logo img');
    var _logoSrc = (_logoEl && _logoEl.src) || '/images/gocare-institute-logo.png';
    const mobDrawHead = document.createElement('div'); mobDrawHead.className = 'mob-drawer-head'; mobDrawHead.innerHTML = '<img loading="eager" decoding="async" src="' + _logoSrc + '" alt="GoCare"><button class="mob-drawer-close" id="mobClose"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>'; document.body.appendChild(mob); mob.prepend(mobDrawHead);
    function openDrawer(){ ham.classList.add('open'); mobOvl.style.display='block'; mob.animate([{transform:'translateX(105vw)'},{transform:'translateX(0)'}],{duration:450,easing:'cubic-bezier(.76,0,.24,1)',fill:'forwards'}); mobOvl.animate([{opacity:0},{opacity:1}],{duration:280,fill:'forwards'}); document.body.style.overflow='hidden'; }
    function closeDrawer(){ ham.classList.remove('open'); document.body.style.overflow=''; mob.animate([{transform:'translateX(0)'},{transform:'translateX(105vw)'}],{duration:450,easing:'cubic-bezier(.76,0,.24,1)',fill:'forwards'}); mobOvl.animate([{opacity:1},{opacity:0}],{duration:280,fill:'forwards'}).onfinish=()=>{mobOvl.style.display='none';}; }
    ham.addEventListener('click', () => ham.classList.contains('open') ? closeDrawer() : openDrawer());
    mob.querySelectorAll('a').forEach(a => a.addEventListener('click', closeDrawer));
    mobOvl.addEventListener('click', closeDrawer);
    document.getElementById('mobClose').addEventListener('click', closeDrawer);
  </script>
  <script src="/script.js"></script>
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
    if (window.lucide) lucide.createIcons();
  </script>
  <script src="/mobile-nav.js"></script>
  <script src="/search-index.js" defer></script>
  <script id="gc-search-js" src="/search.js" defer></script>
  <script src="/accessibility.js" defer></script>
</body>

</html>
