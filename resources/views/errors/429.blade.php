@php
  $code = '429';
  $title = 'Too many requests';
  $eyebrow = 'Slow down a moment';
  $icon = 'gauge';
  $message = 'We received a lot of requests from your device in a short time. Please wait a minute and try again — everything is still working normally.';
  $primaryLabel = 'Back to Home';
  $primaryUrl = '/';
  $secondaryLabel = 'Contact Support';
  $secondaryUrl = '/contact';
  $showLinks = false;
@endphp
@extends('errors.layout')
