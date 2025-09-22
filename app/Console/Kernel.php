<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the command to run monthly on the same day (e.g., 1st of every month)
        $schedule->command('app:insert-start')->monthlyOn(1, '00:00');
        
        // Schedule automatic monthly starts generation on the 1st of each month at 1:00 AM
        $schedule->command('inventory:generate-monthly-starts --type=both')
                 ->monthlyOn(1, '01:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Optional: Generate starts for the current month if they don't exist (daily check)
        $schedule->command('inventory:generate-monthly-starts --type=both')
                 ->daily()
                 ->when(function () {
                     // Only run if current month starts don't exist
                     $monthlyStartService = app(\App\Services\MonthlyStartService::class);
                     $exists = $monthlyStartService->monthlyStartsExist(\Carbon\Carbon::now()->format('Y-m'));
                     return !$exists['any_exists'];
                 })
                 ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
