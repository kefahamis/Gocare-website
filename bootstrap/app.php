<?php

use App\Http\Middleware\ApplyPageSeo;
use App\Http\Middleware\InjectPageLoader;
use App\Http\Middleware\NormalizeInternalLinks;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(NormalizeInternalLinks::class);
        $middleware->append(ApplyPageSeo::class);
        $middleware->append(InjectPageLoader::class);
        $middleware->validateCsrfTokens(except: [
            'mpesa/callback',
            'mpesa/status/result',
            'mpesa/status/timeout',
            'payments/c2b/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
