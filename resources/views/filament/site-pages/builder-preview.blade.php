@php
    $imageUrl = fn (?string $image): ?string => filled($image)
        ? (str_starts_with($image, 'http') || str_starts_with($image, '/') ? $image : '/'.ltrim($image, '/'))
        : null;
    $blocks = collect($sections)->filter(fn ($section) => filled($section['heading'] ?? null) || filled($section['body'] ?? null) || filled($section['link'] ?? null))->values();
@endphp

<div class="gc-builder-preview">
    <div class="gc-builder-preview__bar"><span>GoCare preview</span><span>{{ $title ?: 'Untitled page' }}</span></div>
    <div class="gc-builder-preview__hero"><small>GoCare Training Institute</small><h2>{{ $title ?: 'Your page title' }}</h2><p>Build a clear, confident learner journey with the GoCare design system.</p></div>
    @forelse ($blocks as $block)
        @php $type = $block['type'] ?? 'content'; $image = $imageUrl($block['image'] ?? null); @endphp
        <section class="gc-builder-block gc-builder-block--{{ $type }}">
            @if ($image)<img src="{{ $image }}" alt="">@endif
            <div><small>{{ ucfirst($type) }}</small><h3>{{ $block['heading'] ?? 'Section heading' }}</h3><p>{{ $block['body'] ?? 'Add supporting content for this block.' }}</p>@if (!empty($block['link']))<span class="gc-builder-btn">{{ $block['link_label'] ?? 'Learn More' }}</span>@endif</div>
        </section>
    @empty
        <div class="gc-builder-empty">Add your first block to start building this page.</div>
    @endforelse
</div>

<style>
    .gc-builder-preview{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;font-family:Inter,ui-sans-serif,sans-serif;color:#263244}.gc-builder-preview__bar{display:flex;justify-content:space-between;padding:10px 14px;background:#4a1d6d;color:#fff;font-size:11px;font-weight:700}.gc-builder-preview__hero{padding:28px 24px;background:linear-gradient(120deg,#3d1560,#6c2d8c);color:#fff}.gc-builder-preview__hero small,.gc-builder-block small{color:#ec7424;text-transform:uppercase;font-weight:800;font-size:10px;letter-spacing:.08em}.gc-builder-preview__hero h2{margin:6px 0;font-size:25px}.gc-builder-preview__hero p,.gc-builder-block p{margin:0;font-size:13px;line-height:1.6}.gc-builder-block{display:flex;gap:20px;align-items:center;padding:22px 24px;background:#fff;border-bottom:1px solid #eee}.gc-builder-block:nth-of-type(odd){background:#fff8ed}.gc-builder-block img{width:120px;height:82px;object-fit:cover;border-radius:10px}.gc-builder-block h3{margin:4px 0;font-size:16px}.gc-builder-block--callout{border-left:4px solid #ec7424}.gc-builder-block--cta{background:#f5ebd2}.gc-builder-btn{display:inline-block;margin-top:10px;padding:6px 11px;border-radius:999px;background:#ec7424;color:#fff;font-size:11px;font-weight:700}.gc-builder-empty{padding:32px;text-align:center;color:#64748b;font-size:13px}@media(max-width:640px){.gc-builder-block{padding:18px;gap:12px}.gc-builder-block img{width:84px;height:70px}}
</style>
