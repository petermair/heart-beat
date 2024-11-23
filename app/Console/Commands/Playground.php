<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->functionFailsForSure();
          } catch (\Throwable $exception) {
            \Sentry\captureException($exception);
          }
    }
}
