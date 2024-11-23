<?php

namespace App\Console;

use App\Jobs\ExecuteTestScenarioJob;
use App\Models\TestScenario;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run test scenarios every minute
        $schedule->command('test-scenarios:run')->everyMinute();

        // Schedule individual test scenarios based on their intervals
        TestScenario::query()
            ->where('is_active', true)
            ->get()
            ->each(function (TestScenario $scenario) use ($schedule) {
                $schedule->job(new ExecuteTestScenarioJob($scenario))
                    ->cron("*/{$scenario->interval_seconds} * * * *")
                    ->withoutOverlapping()
                    ->onOneServer();
            });

        // Monitor devices every minute
        $schedule->command('devices:monitor')->everyMinute();
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
