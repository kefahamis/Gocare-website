{{--
  Self-contained error page: no layout, no database and no site partials.
  Used for 500/503 where the app (or its database) may be unavailable, so
  layouts.site — which reads SeoSetting from the database — cannot be rendered.
--}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>{{ $code }} &middot; {{ $title }} | GoCare Training Institute</title>
  <link rel="icon" type="image/png" href="/images/gocare-institute-logo.png">
  <link rel="apple-touch-icon" href="/images/gocare-institute-logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 40px 24px;
      font-family: 'Outfit', 'Segoe UI', system-ui, sans-serif;
      background: linear-gradient(135deg, #4a1d6d 0%, #642a7e 55%, #7d3a9c 100%);
      color: #F5F0E8;
      line-height: 1.6;
    }
    body::before, body::after {
      content: ''; position: fixed; border-radius: 50%; pointer-events: none;
    }
    body::before {
      width: 520px; height: 520px; top: -220px; right: -140px;
      background: radial-gradient(circle, rgba(236,116,36,.35), transparent 68%);
    }
    body::after {
      width: 420px; height: 420px; bottom: -200px; left: -120px;
      background: radial-gradient(circle, rgba(255,255,255,.14), transparent 70%);
    }
    .card { position: relative; z-index: 2; max-width: 640px; text-align: center; }
    .logo { width: 108px; height: auto; margin-bottom: 34px; }
    .eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.28);
      border-radius: 50px; padding: 7px 18px;
      font-size: .78rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
    }
    .eyebrow svg { width: 15px; height: 15px; stroke: #ec7424; }
    .code {
      font-size: clamp(5.5rem, 18vw, 10rem);
      font-weight: 700; line-height: .9;
      margin: 20px 0 6px; letter-spacing: -.03em;
      background: linear-gradient(180deg, #ffffff 12%, #ec7424 108%);
      -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    h1 { font-size: clamp(1.5rem, 4vw, 2.3rem); font-weight: 700; margin-bottom: 14px; }
    p.msg { font-size: 1.05rem; color: rgba(245,240,232,.82); }
    .btns { display: flex; flex-wrap: wrap; justify-content: center; gap: 14px; margin-top: 32px; }
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 28px; border-radius: 50px;
      font-weight: 700; font-size: .875rem;
      text-decoration: none; border: none; cursor: pointer;
      font-family: inherit; transition: all .25s;
    }
    .btn-primary {
      background: #ec7424; color: #F5F0E8;
      box-shadow: 0 4px 14px rgba(237,116,37,.3);
    }
    .btn-primary:hover { background: #d05d15; transform: translateY(-2px); }
    .btn-ghost {
      background: rgba(255,255,255,.1); color: #F5F0E8;
      border: 1.5px solid rgba(255,255,255,.45);
    }
    .btn-ghost:hover { background: rgba(255,255,255,.22); transform: translateY(-2px); }
    .help { margin-top: 38px; font-size: .9rem; color: rgba(245,240,232,.7); }
    .help a { color: #ec7424; font-weight: 700; text-decoration: none; }
    .help a:hover { text-decoration: underline; }
  </style>
</head>

<body>
  <div class="card">
    <img class="logo" src="/images/gocare-institute-logo.png" alt="GoCare Training Institute">
    <span class="eyebrow">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
      {{ $eyebrow }}
    </span>
    <div class="code">{{ $code }}</div>
    <h1>{{ $title }}</h1>
    <p class="msg">{{ $message }}</p>
    <div class="btns">
      <button class="btn btn-primary" onclick="window.location.reload()">Try Again</button>
      <a class="btn btn-ghost" href="/">Back to Home</a>
    </div>
    <p class="help">
      Need us urgently? Call <a href="tel:+254703115502">+254 703 115 502</a>
      or email <a href="mailto:info@gocareinstitute.ac.ke">info@gocareinstitute.ac.ke</a>.
    </p>
  </div>
</body>

</html>
