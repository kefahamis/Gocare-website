@php
  $heroTitle = trim(\Illuminate\Support\Str::before(\Illuminate\Support\Str::before($record->title, '|'), ' —'));
  $metaTitle = $heroTitle . ' | ' . \App\Models\SeoSetting::current()->site_name;
  $bodyText = trim(collect($record->sections ?? [])->map(fn ($s) => strip_tags($s['body'] ?? ''))->implode(' '));
  $metaDescription = mb_substr($bodyText ?: strip_tags($record->content ?? ''), 0, 160);
  $heroIntro = filled($record->content) ? \Illuminate\Support\Str::limit(strip_tags($record->content), 180) : null;
@endphp
@extends('layouts.site')

@section('content')
  <x-page-content
      :sections="[]"
      :hero-title="$heroTitle"
      :hero-intro="$heroIntro"
      :hero-image="$record->image"
      :hero-fallback-image="'/images/new-images/courses/hero-courses.jpeg'"
      :legacy-content="$record->content"
  />
@endsection
