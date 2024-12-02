<?php

namespace App\Console\Commands;

use App\Models\TestScenario;
use App\Services\MessageFlow\FlowExecutionService;
use Illuminate\Console\Command;

class RunTestScenariosCommand extends Command
{
    protected $signature = 'test-scenarios:run
                          {--scenario-id= : ID of a specific test scenario to run}';

    protected $description = 'Run test scenarios based on their configured intervals';

    public function __construct(
        private readonly FlowExecutionService $flowExecutionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = TestScenario::query()
            ->where('is_active', true);

        // Filter by scenario ID if provided
        if ($scenarioId = $this->option('scenario-id')) {
            $query->where('id', $scenarioId);
        }

        $scenarios = $query->get();

        $this->info("Found {$scenarios->count()} active test scenarios to run.");

        foreach ($scenarios as $scenario) {
            $this->info("Running test scenario: {$scenario->name}");
            
            try {
                $testResult = $this->flowExecutionService->startTest($scenario);
                $this->info("Successfully started test scenario: {$scenario->name} (Test Result ID: {$testResult->id})");
            } catch (\Exception $e) {
                $this->error("Failed to run test scenario {$scenario->name}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
