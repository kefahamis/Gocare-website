<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MediaPickerController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Models\Slider;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home', [
    'sliders' => Slider::published()->orderBy('sort_order')->get(),
]))->name('home');

Route::view('/contact', 'contact')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');

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
