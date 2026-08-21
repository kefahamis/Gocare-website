@php
  $code = '419';
  $title = 'Your session expired';
  $eyebrow = 'Session timed out';
  $icon = 'timer-off';
  $message = 'You were away a little too long, so the form you submitted is no longer valid. Refresh the page and fill it in again — your details were not saved.';
  $primaryLabel = 'Reload the Page';
  $primaryUrl = url()->previous('/');
  $secondaryLabel = 'Start an Application';
  $secondaryUrl = '/apply';
  $showLinks = false;
@endphp
@extends('errors.layout')
