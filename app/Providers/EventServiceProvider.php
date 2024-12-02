<?php

namespace App\Providers;

use App\Models\TestResult;
use App\Observers\TestResultObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        TestResult::observe(TestResultObserver::class);
    }
}
