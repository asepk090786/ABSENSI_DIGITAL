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
        if (app()->environment('testing')) {
            return;
        }

        // Schedule database backup if enabled in settings
        try {
            $settings = new SettingsManager();
            if ($settings->get('backup.enabled', false)) {
                $time = $settings->get('backup.time', '02:00');
                $format = $settings->get('backup.format', 'zip');

                // Keep existing daily backup behavior intact.
                $schedule->command('db:backup --format=' . ($format === 'zip' ? 'zip' : 'sql'))
                    ->dailyAt($time)
                    ->withoutOverlapping();

                // Additional backup interval during working hours: every 15 minutes from 07:00 to 15:00.
                $schedule->command('db:backup --format=' . ($format === 'zip' ? 'zip' : 'sql'))
                    ->cron('*/15 7-15 * * *')
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
