<?php

namespace App\Console\Commands;

use App\Models\TestScenario;
use App\Services\TestExecution\TestExecutionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunTestScenarios extends Command
{
    protected $signature = 'test-scenarios:run {--scenario=} {--force}';
    protected $description = 'Run test scenarios to monitor service health';

    public function handle(TestExecutionService $testExecutionService): int
    {
        $scenarioId = $this->option('scenario');
        $force = $this->option('force');

        try {
            if ($scenarioId) {
                // Run specific scenario
                $scenario = TestScenario::findOrFail($scenarioId);
                if (!$scenario->is_active && !$force) {
                    $this->warn("Scenario {$scenario->id} is not active. Use --force to run anyway.");
                    return 0;
                }
                $testExecutionService->executeScenario($scenario);
                $this->info("Executed scenario {$scenario->id}");
            } else {
                // Run all active scenarios
                $scenarios = TestScenario::where('is_active', true)->get();
                foreach ($scenarios as $scenario) {
                    $testExecutionService->executeScenario($scenario);
                    $this->info("Executed scenario {$scenario->id}");
                }
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to run test scenarios', [
                'error' => $e->getMessage(),
                'scenario_id' => $scenarioId,
            ]);
            $this->error($e->getMessage());
            return 1;
        }
    }
}
