<?php

namespace EliteHub\Payment\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/payments.php', 'payments');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/payments.php' => config_path('payments.php'),
        ], 'payments-config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
