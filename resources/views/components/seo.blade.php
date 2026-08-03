@php($seo = \App\Models\SeoSetting::current())
@php($metaTitle = $metaTitle ?? $seo->default_title)
@php($metaDescription = $metaDescription ?? $seo->meta_description)
@php($metaImage = $metaImage ?? $seo->og_image)
<meta name="description" content="{{ $metaDescription }}">
@if ($seo->keywords)
  <meta name="keywords" content="{{ $seo->keywords }}">
@endif
<meta name="robots" content="{{ $seo->robots ?: 'index,follow' }}">
<link rel="canonical" href="{{ $seo->canonical_url ? rtrim($seo->canonical_url, '/') . request()->getPathInfo() : url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $seo->site_name }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
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
