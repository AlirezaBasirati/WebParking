<?php

namespace App\Providers;

use App\Repositories\Contracts\ClientRequestLogRepositoryInterface;
use App\Repositories\Contracts\ExternalServiceCallLogRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Eloquent\ClientRequestLogRepository;
use App\Repositories\Eloquent\ExternalServiceCallLogRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Services\ExactOnline\ExactOnlineInterface;
use App\Services\ExactOnline\MockExactOnlineService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            InvoiceRepository::class
        );
        $this->app->bind(
            ClientRequestLogRepositoryInterface::class,
            ClientRequestLogRepository::class
        );
        $this->app->bind(
            ExternalServiceCallLogRepositoryInterface::class,
            ExternalServiceCallLogRepository::class
        );
        $this->app->bind(
            ExactOnlineInterface::class,
            fn($app) => new MockExactOnlineService(config('services.exact'))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
