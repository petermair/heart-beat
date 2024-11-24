<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\TestResult;
use App\Models\TestScenario;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run active test scenarios at their configured intervals
if (app()->environment() !== 'testing') {
    TestScenario::where('is_active', true)->each(function ($scenario) {
        Schedule::command("test-scenarios:run --scenario={$scenario->id}")
            ->cron("*/{$scenario->interval_seconds} * * * *")
            ->withoutOverlapping()
            ->runInBackground();
    });
}

// Clean up old test results (keep last 7 days)
if (app()->environment() !== 'testing') {
    Schedule::command('model:prune', [
        '--model' => [TestResult::class],
        '--max-age' => '7 days',
    ])->daily();
}
