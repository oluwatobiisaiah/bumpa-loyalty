<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentProviderInterface;
use App\Services\Payment\Providers\MockPaymentProvider;
use App\Services\Payment\Providers\PaystackProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Payment Provider Interface to concrete implementation
        $this->app->bind(PaymentProviderInterface::class, function ($app) {
            $provider = config('services.payment.provider', 'mock');

            return match($provider) {
                'paystack' => new PaystackProvider(),
                // 'flutterwave' => new FlutterwaveProvider(),
                default => new MockPaymentProvider(),
            };
        });

        // Optional: Register services as singletons for better performance
        $this->app->singleton(\App\Services\Loyalty\AchievementService::class);
        $this->app->singleton(\App\Services\Loyalty\BadgeService::class);
        $this->app->singleton(\App\Services\Payment\CashbackPaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
