<?php

use App\Models\TestResult;
use App\Models\TestScenario;
use Illuminate\Support\Facades\Schedule;

if (app()->environment() !== 'testing') {
    // Run test scenarios at their configured intervals
    TestScenario::where('is_active', true)->each(function ($scenario) {
        Schedule::command("test-scenarios:run --scenario-id={$scenario->id}")
            ->cron("*/{$scenario->interval_seconds} * * * *")
            ->withoutOverlapping();
    });

    // Process message flow status every minute
    Schedule::command('message-flow:process-status')
        ->everyMinute()
        ->withoutOverlapping();

    // Clean up old test results (keep last 7 days)
    Schedule::command('model:prune', [
        '--model' => [TestResult::class],
        '--max-age' => '7 days',
    ])->daily();
}
