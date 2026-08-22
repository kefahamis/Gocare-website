@php($seo = \App\Models\SeoSetting::current())
@php($page = \App\Models\PageSeo::forPath(request()->getPathInfo()))
{{-- Precedence: the per-page record, then anything the controller passed in,
     then the site-wide default. --}}
@php($metaTitle = $page?->meta_title ?: ($metaTitle ?? $seo->default_title))
@php($metaDescription = $page?->meta_description ?: ($metaDescription ?? $seo->meta_description))
@php($metaImage = $page?->og_image ?: ($metaImage ?? $seo->og_image))
@php($metaKeywords = $page?->keywords ?: $seo->keywords)
{{-- data-gc-seo tells ApplyPageSeo whether this description came from the page
     record or the site-wide fallback, so it knows which of the duplicates the
     static views emit is the one worth keeping. --}}
<meta name="description" data-gc-seo="{{ $page?->meta_description ? 'page' : 'default' }}" content="{{ $metaDescription }}">
@if ($metaKeywords)
  <meta name="keywords" content="{{ $metaKeywords }}">
@endif
<meta name="robots" content="{{ $seo->robots ?: 'index,follow' }}">
{{-- Per-page canonical first, for content reachable at more than one URL;
     otherwise this page's own address, built on the configured site root. --}}
@php($canonical = $page?->canonicalUrl($seo->canonical_url) ?: ($seo->canonical_url ? rtrim($seo->canonical_url, '/') . request()->getPathInfo() : url()->current()))
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $seo->site_name }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($metaImage)
  <meta property="og:image" content="{{ str_starts_with($metaImage, 'http') ? $metaImage : asset($metaImage) }}">
@endif
<meta name="twitter:card" content="summary_large_image">
@if ($seo->twitter_handle)
  <meta name="twitter:site" content="{{ $seo->twitter_handle }}">
@endif
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if ($metaImage)
  <meta name="twitter:image" content="{{ str_starts_with($metaImage, 'http') ? $metaImage : asset($metaImage) }}">
@endif
@if ($seo->google_site_verification)
  <meta name="google-site-verification" content="{{ $seo->google_site_verification }}">
@endif
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'EducationalOrganization',
    'name' => $seo->organization_name ?: $seo->site_name,
    'url' => url('/'),
    'logo' => $seo->organization_logo ? (str_starts_with($seo->organization_logo, 'http') ? $seo->organization_logo : asset($seo->organization_logo)) : null,
    'telephone' => $seo->organization_phone,
    'email' => $seo->organization_email,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@if ($pageSchema = $page?->decodedStructuredData())
  {{-- Page-specific schema.org, alongside the organisation block above. --}}
  <script type="application/ld+json">
{!! json_encode($pageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
  </script>
@endif
