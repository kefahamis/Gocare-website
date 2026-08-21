<?php

namespace App\Providers;

use App\Http\Controllers\MpesaVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the manual application-fee payment routes.
 *
 * These live here rather than in routes/web.php so the feature does not touch
 * a file the STK push also relies on, and so the Safaricom callbacks can skip
 * the web middleware group entirely instead of needing a CSRF exemption.
 */
class ApplicationPaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Browser-facing: needs the session for the CSRF token and claim key.
        Route::middleware('web')
            ->prefix('mpesa/verification')
            ->name('mpesa.verification.')
            ->group(function () {
                Route::post('/query', [MpesaVerificationController::class, 'verify'])
                    ->middleware('throttle:10,1')
                    ->name('query');

                Route::get('/status/{token}', [MpesaVerificationController::class, 'status'])
                    ->middleware('throttle:60,1')
                    ->whereUuid('token')
                    ->name('status');
            });

        // Safaricom posts these server-to-server. No session, so no CSRF token
        // to validate and no exemption needed.
        Route::prefix('mpesa/verification/callback')
            ->name('mpesa.verification.callback.')
            ->group(function () {
                Route::post('/result/{secret?}', [MpesaVerificationController::class, 'result'])->name('result');
                Route::post('/timeout/{secret?}', [MpesaVerificationController::class, 'timeout'])->name('timeout');
            });
    }
}
