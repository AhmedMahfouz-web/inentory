<?php

namespace App\Providers;

use App\Models\Branch;
use Illuminate\Support\Facades\View;
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
        try {
            View::share('branches', Branch::all());
        } catch (\Throwable $e) {
            View::share('branches', collect());
        }
    }
}
