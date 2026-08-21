<?php

use App\Providers\AppServiceProvider;
use App\Providers\ApplicationPaymentServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    ApplicationPaymentServiceProvider::class,
    AdminPanelProvider::class,
];
