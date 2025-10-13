<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\VendorGenerateSlugs::class,
        \App\Console\Commands\VendorSlugScan::class,
        \App\Console\Commands\VendorGenerateWebp::class,
        \App\Console\Commands\DispatchWebpJobs::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Dispatch webp generation jobs every 5 minutes (ensure queue worker is running)
        $schedule->command('webp:dispatch')->everyFiveMinutes();
    }

    protected function commands()
    {
        // Load console routes if present
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
