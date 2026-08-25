<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Mpesa\C2bController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MediaPickerController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
Route::post('/applications/submit', [ApplicationController::class, 'submitDetails'])->name('applications.submit');
Route::post('/applications/manual', [ApplicationController::class, 'manualPayment'])->name('applications.manual');
Route::post('/applications/paid', [ApplicationController::class, 'confirmManualPayment'])->name('applications.paid');
Route::post('/applications/stk-query', [ApplicationController::class, 'queryStkPush'])->name('applications.stk-query');
Route::get('/applications/status/{reference}', [ApplicationController::class, 'status'])->name('applications.status');
Route::post('/mpesa/callback', [ApplicationController::class, 'mpesaCallback'])->name('mpesa.callback');
Route::post('/mpesa/status/result', [ApplicationController::class, 'mpesaStatusCallback'])->name('mpesa.status.result');
Route::post('/mpesa/status/timeout', [ApplicationController::class, 'mpesaStatusTimeout'])->name('mpesa.status.timeout');

// C2B. Deliberately NOT under /mpesa: Daraja rejects registered C2B URLs
// containing "mpesa", "safaricom", "exe", "cmd", "sql" or "query". The {token}
// segment is the shared secret from config('mpesa.c2b.token').
Route::post('/payments/c2b/{token}/confirm', [C2bController::class, 'confirm'])->name('c2b.confirm');
Route::post('/payments/c2b/{token}/validate', [C2bController::class, 'validatePayment'])->name('c2b.validate');

Route::get('/courses', [PageController::class, 'courses'])->name('courses');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');

Route::get('/courses/{slug}', [PageController::class, 'category'])
    ->where('slug', '[a-z0-9-]+')
    ->defaults('category', 'courses')
    ->name('courses.show');

Route::get('/blog/{slug}', [PageController::class, 'category'])
    ->where('slug', '[a-z0-9-]+')
    ->defaults('category', 'blog')
    ->name('blog.show');

Route::get('/schools/{slug}', [PageController::class, 'category'])
    ->where('slug', '[a-z0-9-]+')
    ->defaults('category', 'schools')
    ->name('schools.show');

Route::get('/admissions/{slug}', [PageController::class, 'category'])
    ->where('slug', '[a-z0-9-]+')
    ->defaults('category', 'admissions')
    ->name('admissions.show');

Route::get('/about/{slug}', [PageController::class, 'category'])
    ->where('slug', '[a-z0-9-]+')
    ->defaults('category', 'about')
    ->name('about.show');

Route::get('/media/browse', [MediaPickerController::class, 'browse'])
    ->name('media.browse');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('pages.show');
