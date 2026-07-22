<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\SettingsManager;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule database backup if enabled in settings
        try {
            $settings = new SettingsManager();
            if ($settings->get('backup.enabled', false)) {
                $time = $settings->get('backup.time', '02:00');
                $format = $settings->get('backup.format', 'zip');
                // schedule daily at configured time
                $schedule->command('db:backup --format=' . ($format === 'zip' ? 'zip' : 'sql'))
                    ->dailyAt($time)
                    ->withoutOverlapping();
            }
        } catch (\Throwable $e) {
            // ignore scheduling errors
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
