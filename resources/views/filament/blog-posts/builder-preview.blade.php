@php
    $imageUrl = fn (?string $image): ?string => filled($image)
        ? (str_starts_with($image, 'http') || str_starts_with($image, '/') ? $image : '/'.ltrim($image, '/'))
        : null;
    $blocks = collect($sections)->filter(fn ($section) => filled($section['heading'] ?? null) || filled($section['body'] ?? null) || filled($section['link'] ?? null))->values();
@endphp

<div class="gb-builder-preview">
    <div class="gb-builder-preview__bar"><span>GoCare blog preview</span><span>{{ $title ?: 'Untitled post' }}</span></div>
    <div class="gb-builder-preview__body">
        <small class="gb-builder-preview__tag">GoCare Blog</small>
        <h2 class="gb-builder-preview__title">{{ $title ?: 'Your post title' }}</h2>
        @if (filled($excerpt ?? null))
            <p class="gb-builder-preview__subtitle">{{ $excerpt }}</p>
        @endif
        @forelse ($blocks as $block)
            @php $type = $block['type'] ?? 'content'; $image = $imageUrl($block['image'] ?? null); @endphp
            <section class="gb-builder-block gb-builder-block--{{ $type }}">
                @if ($image)<img src="{{ $image }}" alt="">@endif
                <div>
                    <small>{{ ucfirst($type) }}</small>
                    <h3>{{ $block['heading'] ?? 'Section heading' }}</h3>
                    <p>{{ $block['body'] ?? 'Add supporting content for this block.' }}</p>
                    @if (!empty($block['link']))<span class="gb-builder-btn">{{ $block['link_label'] ?? 'Learn More' }}</span>@endif
                </div>
            </section>
        @empty
            <div class="gb-builder-empty">Add your first block to start building this post.</div>
        @endforelse
    </div>
</div>

<style>
    .gb-builder-preview{max-width:820px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;font-family:Inter,ui-sans-serif,sans-serif;color:#263244}.gb-builder-preview__bar{display:flex;justify-content:space-between;padding:10px 14px;background:#4a1d6d;color:#fff;font-size:11px;font-weight:700}.gb-builder-preview__body{padding:26px 28px 32px;background:#fff}.gb-builder-preview__tag{display:inline-flex;align-items:center;gap:7px;background:var(--ol,#f5ebd2);color:var(--o,#ec7424);padding:5px 16px;border-radius:50px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px}.gb-builder-preview__title{margin:0 0 8px;font-size:26px;font-weight:900}.gb-builder-preview__subtitle{margin:0 0 22px;font-size:14px;color:#6B7280}.gb-builder-block{display:flex;gap:20px;align-items:center;padding:22px 24px;background:#fff;border:1px solid #eee;border-radius:12px;margin-bottom:12px}.gb-builder-block small{color:#ec7424;text-transform:uppercase;font-weight:800;font-size:10px;letter-spacing:.08em}.gb-builder-block h3{margin:4px 0;font-size:16px}.gb-builder-block p{margin:0;font-size:13px;line-height:1.6}.gb-builder-block img{width:120px;height:82px;object-fit:cover;border-radius:10px}.gb-builder-block--callout{background:var(--ol,#f5ebd2);border-left:4px solid var(--o,#ec7424)}.gb-builder-block--cta{background:linear-gradient(135deg,#2d0f3c,#4a1d6d);color:#fff}.gb-builder-block--cta small,.gb-builder-block--cta h3{color:#fff}.gb-builder-btn{display:inline-block;margin-top:10px;padding:6px 11px;border-radius:999px;background:#ec7424;color:#fff;font-size:11px;font-weight:700}.gb-builder-empty{padding:32px;text-align:center;color:#64748b;font-size:13px;border:1px dashed #ddd;border-radius:12px}@media(max-width:640px){.gb-builder-block{padding:18px;gap:12px}.gb-builder-block img{width:84px;height:70px}}
</style>
