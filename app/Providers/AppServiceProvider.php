<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SMTP settings edited in the admin panel override .env, but only
        // once switched on there -- see App\Models\MailSetting.
        \App\Models\MailSetting::applyToConfig();

        // Support the 1000-byte index limit used by older WAMP MySQL builds.
        Schema::defaultStringLength(191);
    }
}
