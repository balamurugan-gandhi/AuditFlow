<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\ClientRepositoryInterface::class,
            \App\Repositories\ClientRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\FileRepositoryInterface::class,
            \App\Repositories\FileRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\WorkLogRepositoryInterface::class,
            \App\Repositories\WorkLogRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\DocumentRepositoryInterface::class,
            \App\Repositories\DocumentRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\InvoiceRepositoryInterface::class,
            \App\Repositories\InvoiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PaymentRepositoryInterface::class,
            \App\Repositories\PaymentRepository::class
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
