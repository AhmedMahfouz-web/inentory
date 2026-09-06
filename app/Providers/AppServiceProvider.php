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

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Any logged-in user can access the main dashboard
            if ($ability === 'dashboard-show') {
                return true;
            }

            if (
                $user->hasRole('admin') ||
                in_array($user->username, ['admin', 'abdallah']) ||
                in_array($user->id, [1, 4])
            ) {
                return true;
            }
            return null;
        });
    }
}
