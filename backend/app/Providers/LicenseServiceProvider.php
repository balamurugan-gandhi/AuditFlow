<?php

namespace App\Providers;

use App\Services\LicenseManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LicenseManager::class, function ($app) {
            return new LicenseManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $license = $this->app->make(LicenseManager::class);

        // Define Gates for feature-based access
        // Example: Gate::allows('license-feature', 'export')
        Gate::define('license-feature', function ($user, string $feature) use ($license) {
            return $license->allows($feature);
        });

        // Specific feature shortcuts
        Gate::define('can-export', function ($user) use ($license) {
            return $license->allows('export');
        });

        Gate::define('can-whatsapp', function ($user) use ($license) {
            return $license->allows('whatsapp');
        });

        // Blade directive for feature gating
        Blade::if('licensed', function (string $feature) use ($license) {
            return $license->allows($feature);
        });
        
        // Share license status with all views if needed
        view()->share('license_valid', $license->isValid());
        view()->share('license_error', $license->getError());
    }
}
