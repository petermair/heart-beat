<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteTestScenarioJob;
use App\Models\TestScenario;
use Illuminate\Console\Command;

class RunTestScenariosCommand extends Command
{
    protected $signature = 'test-scenarios:run
                          {--scenario-id= : ID of a specific test scenario to run}
                          {--device-id= : Run all scenarios for a specific device}';

    protected $description = 'Run test scenarios based on their configured intervals';

    public function handle(): int
    {
        $query = TestScenario::query()
            ->where('is_active', true);

        // Filter by scenario ID if provided
        if ($scenarioId = $this->option('scenario-id')) {
            $query->where('id', $scenarioId);
        }

        // Filter by device ID if provided
        if ($deviceId = $this->option('device-id')) {
            $query->where('device_id', $deviceId);
        }

        $scenarios = $query->get();

        $this->info("Found {$scenarios->count()} active test scenarios to run.");

        foreach ($scenarios as $scenario) {
            $this->info("Dispatching test scenario: {$scenario->name}");
            ExecuteTestScenarioJob::dispatch($scenario);
        }

        return self::SUCCESS;
    }
}
