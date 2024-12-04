<?php

namespace App\Services\MessageFlow;

use App\Enums\TestResultStatus;
use App\Models\TestResult;
use App\Models\MessageFlow;
use App\Models\DeviceMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MessageFlowStatusService
{
    private ServiceFailureAnalyzer $serviceFailureAnalyzer;
    private ServiceStatusService $statusService;

    public function __construct(
        ServiceFailureAnalyzer $failureAnalyzer,
        ServiceStatusService $statusService
    ) {
        $this->serviceFailureAnalyzer = $failureAnalyzer;
        $this->statusService = $statusService;
    }

    /**
     * Process a test result and update service statuses
     */
    public function processTestResult(TestResult $testResult): void
    {
        info("Processing TestResult ID: " . $testResult->id);
        $flows = $testResult->messageFlows;
        
        // If no flows exist yet, return early
        if (!$flows) {
            info("No flows found for TestResult ID: " . $testResult->id);
            return;
        }
        
        info("Found " . $flows->count() . " flows for TestResult ID: " . $testResult->id);
        
        // Check for timeouts and mark as failures
        $flows->each(function($flow) {
            if ($this->isTimeout($flow)) {
                info("Flow ID: " . $flow->id . " has timed out, marking as FAILURE");
                $flow->status = TestResultStatus::FAILURE->value;
                $flow->save();
                
                // Create or update device message for timeout
                $timeoutMinutes = config('app.message_flow_timeout_minutes', 5);
                DeviceMessage::updateOrCreate(
                    [
                        'message_flow_id' => $flow->id,
                    ],
                    [
                        'device_id' => $flow->testResult->device_id,
                        'source' => 'TIMEOUT',
                        'success' => false,
                        'error_message' => "Flow timed out after {$timeoutMinutes} minutes",
                        'response_time_ms' => $timeoutMinutes * 60 * 1000,
                        'metadata' => json_encode(['timeout_minutes' => $timeoutMinutes]),
                    ]
                );
            }
        });

        // Only process if all flows are complete (SUCCESS, FAILURE, or timed out)
        if (!$this->areAllFlowsComplete($flows)) {
            info("Not all flows are complete for TestResult ID: " . $testResult->id);
            $flows->each(function($flow) {
                info("Flow ID: " . $flow->id . " Status: " . $flow->status->value);
            });
            return;
        }

        info("All flows are complete for TestResult ID: " . $testResult->id);

        // Analyze failures and update service statuses
        $this->serviceFailureAnalyzer->analyzePotentialFailures($flows);
        
        // Set test result status based on flow statuses
        $failedFlows = $flows->filter(fn($flow) => 
            $flow->status->value === TestResultStatus::FAILURE->value
        );
        
        $hasFailures = $failedFlows->isNotEmpty();
        if ($hasFailures) {
            $errorMessages = $failedFlows->map(fn($flow) => 
                "Flow {$flow->id} ({$flow->flow_type->label()}) failed"
            )->join(', ');
            
            info("TestResult ID: " . $testResult->id . " failed with errors: " . $errorMessages);
            $testResult->error_message = $errorMessages;
        }
        
        $testResult->status = $hasFailures ? 
            TestResultStatus::FAILURE->value : 
            TestResultStatus::SUCCESS->value;
        
        info("Setting TestResult ID: " . $testResult->id . " status to: " . $testResult->status->value);
        $testResult->save();
        
        // Update service metrics after status is set
        info("Updating service metrics for TestResult ID: " . $testResult->id);
        $this->statusService->updateServiceMetrics($testResult);
    }

    /**
     * Check if all flows in a test result are complete (SUCCESS, FAILURE, or timed out)
     */
    private function areAllFlowsComplete(?Collection $flows): bool
    {
        if (!$flows || $flows->isEmpty()) {
            info("Flows collection is null or empty");
            return false;
        }
        
        $pendingFlows = $flows->filter(function($flow) {
            $isPending = $flow->status->value === TestResultStatus::PENDING->value && !$this->isTimeout($flow);
            if ($isPending) {
                info("Flow ID: " . $flow->id . " is still pending and not timed out");
            }
            return $isPending;
        });
        
        return $pendingFlows->isEmpty();
    }

    /**
     * Check if a message flow has timed out
     */
    private function isTimeout(MessageFlow $flow): bool
    {
        $timeoutMinutes = config('app.message_flow_timeout_minutes', 5);
        $minutesSinceCreation = $flow->created_at->diffInMinutes(now());
        $isTimeout = $flow->status->value === TestResultStatus::PENDING->value 
            && $minutesSinceCreation >= $timeoutMinutes;
            
        if ($isTimeout) {
            info("Flow ID: " . $flow->id . " has timed out after " . $minutesSinceCreation . " minutes");
        }
        
        return $isTimeout;
    }
}
