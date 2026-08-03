@php
  $primaryItems = \App\Models\Menu::frontendItems('primary');
  $mobileItems = \App\Models\Menu::frontendItems('mobile');
@endphp

<style>
  /* Mega menu variant used by the menu builder (mirrors the site's standard mega menus) */
  .gc-mega {
    width: 620px !important;
    padding: 0 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .15) !important;
  }
  .gc-mega .res-body {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 0 !important;
    padding: 12px !important;
  }
  .gc-mega .res-body a {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 12px !important;
    border-radius: 10px !important;
    transition: background .15s !important;
    width: 100% !important;
  }
  .gc-mega .res-body a:hover {
    background: #f5f0ff !important;
    padding-left: 12px !important;
  }
  .gc-mega .res-body a:hover .rm-text strong { color: var(--p) !important; }
  .gc-mega .res-body a:hover .rm-ico { transform: scale(1.1); }
</style>

<!-- NAVBAR -->
<nav class="nav" id="mainNav">
  <div class="nav-inner">
    <a href="/" class="logo">
      <img loading="eager" decoding="async" src="/images/gocare-institute-logo.png" width="100" />
    </a>
    <div class="nav-links">
      @foreach ($primaryItems as $item)
        @php
          $children = $item['children'] ?? [];
          $url = ($item['url'] ?? '#') ?: '#';
          $target = $item['target'] ?? null;
          $mega = in_array($item['mega'] ?? null, ['standard', 'resources'], true) ? $item['mega'] : 'standard';
        @endphp
        @if (! empty($children))
          <div class="nav-item">
            <a href="{{ $url }}"@if ($target === '_blank') target="_blank" rel="noopener noreferrer"@endif>{{ $item['label'] }} <i data-lucide="chevron-down"></i></a>
            <div class="dropdown {{ $mega === 'resources' ? 'resources-mega' : 'gc-mega' }}">
              <div class="res-header">
                <span class="res-hd-icon"><i data-lucide="{{ $item['icon'] ?? 'menu' }}"></i></span>
                <div class="res-hd-text">
                  <strong>{{ $item['header_label'] ?? $item['label'] }}</strong>
                  @if (! empty($item['description']))<small>{{ $item['description'] }}</small>@endif
                </div>
              </div>
              <div class="res-body">
                @foreach ($children as $child)
                  <a href="{{ ($child['url'] ?? '#') ?: '#' }}"@if (($child['target'] ?? null) === '_blank') target="_blank" rel="noopener noreferrer"@endif>
                    <span class="rm-ico rm-ico--{{ $child['color'] ?? 'indigo' }}"><i data-lucide="{{ $child['icon'] ?? 'arrow-right' }}"></i></span>
                    <span class="rm-text">
                      <strong>{{ $child['label'] }}</strong>
                      @if (! empty($child['description']))<small>{{ $child['description'] }}</small>@endif
                    </span>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        @else
          <div class="nav-item"><a href="{{ $url }}"@if ($target === '_blank') target="_blank" rel="noopener noreferrer"@endif>{{ $item['label'] }}</a></div>
        @endif
      @endforeach
    </div>
    <div class="nav-right">
      <a href="/apply" class="nav-apply-btn">Start Your Journey <i data-lucide="arrow-right"></i></a>
      <div class="ham" id="hamBtn"><span></span><span></span><span></span></div>
    </div>
  </div>
  <div class="mob-menu" id="mobMenu">
    @foreach ($mobileItems as $item)
      <a href="{{ ($item['url'] ?? '#') ?: '#' }}"@if (($item['target'] ?? null) === '_blank') target="_blank" rel="noopener noreferrer"@endif>{{ $item['label'] }}</a>
    @endforeach
    <a href="/apply" class="btn btn-primary mob-cta">Start Your Journey <i data-lucide="arrow-right"></i></a>
  </div>
</nav>

<!-- SUB BAR -->
<div class="subbar">
  <div class="wrap subbar-inner">
    <span class="subbar-tagline">Train With The Experts... Become an Expert!</span>
    <div class="subbar-portals">
      <a href="#" class="subbar-btn subbar-btn--student">Student Portal</a>
      <a href="#" class="subbar-btn subbar-btn--staff">Staff Portal</a>
    </div>
  </div>
</div>
