<?php

namespace Tests;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->register(\App\Providers\EventServiceProvider::class);
            
            // Disable telescope
            config(['telescope.enabled' => false]);
            
            // Skip telescope migrations
            $this->app->bind('telescope.migrations', function () {
                return false;
            });
        }
    }
}
