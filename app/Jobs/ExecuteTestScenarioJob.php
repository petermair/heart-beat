<?php

namespace App\Jobs;

use App\Models\TestScenario;
use App\Services\Monitoring\TestExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteTestScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        private readonly TestScenario $scenario
    ) {}

    public function handle(TestExecutionService $executionService): void
    {
        Log::info('Starting test scenario execution', [
            'scenario_id' => $this->scenario->id,
            'test_type' => $this->scenario->test_type,
            'device_id' => $this->scenario->device_id,
        ]);

        try {
            $result = $executionService->executeTest($this->scenario);

            // Check if we need to send notifications based on the result
            if (! $result->success && $this->scenario->notification_settings) {
                // TODO: Implement notification sending
                Log::warning('Test scenario failed - notification would be sent', [
                    'scenario_id' => $this->scenario->id,
                    'result_id' => $result->id,
                    'error' => $result->error_message,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to execute test scenario', [
                'scenario_id' => $this->scenario->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Test scenario job failed', [
            'scenario_id' => $this->scenario->id,
            'error' => $exception->getMessage(),
        ]);

        // TODO: Send notification about job failure
    }
}
