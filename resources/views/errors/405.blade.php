@php
  $code = '405';
  $title = 'That action is not allowed here';
  $eyebrow = 'Method not allowed';
  $icon = 'ban';
  $message = 'The request reached us in a way this page does not accept. This usually means a form was submitted twice or a link was followed out of order.';
  $primaryLabel = 'Back to Home';
  $primaryUrl = '/';
  $secondaryLabel = 'Contact Support';
  $secondaryUrl = '/contact';
@endphp
@extends('errors.layout')
