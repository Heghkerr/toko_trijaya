<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\InventoryBadgeComposer;

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
        // Register View Composer untuk inventory badge
        View::composer('layouts.app', InventoryBadgeComposer::class);
        
        // Force HTTPS untuk semua URL jika menggunakan HTTPS
        if (request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
