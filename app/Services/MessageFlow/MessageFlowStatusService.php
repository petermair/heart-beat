<?php

namespace App\Services\MessageFlow;

use App\Enums\TestResultStatus;
use App\Models\TestResult;
use App\Models\MessageFlow;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MessageFlowStatusService
{
    private ServiceFailureAnalyzer $failureAnalyzer;
    private ServiceStatusService $statusService;

    public function __construct(
        ServiceFailureAnalyzer $failureAnalyzer,
        ServiceStatusService $statusService
    ) {
        $this->failureAnalyzer = $failureAnalyzer;
        $this->statusService = $statusService;
    }

    /**
     * Process a single test result and its message flows
     */
    public function processTestResult(TestResult $testResult): void
    {
        // Check for timeout
        if ($this->isTimeout($testResult)) {
            $testResult->status = TestResultStatus::TIMEOUT;
            $testResult->save();
            return;
        }

        // Get message flows
        $flows = $testResult->messageFlows;
        
        // Analyze service failures
        $failedServices = $this->failureAnalyzer->analyzePotentialFailures($flows);
        
        // Update service status
        foreach ($failedServices as $serviceType) {
            $this->statusService->updateServiceMetrics($testResult, $testResult->execution_time_ms, $serviceType);
        }
        
        // Update test result status
        $testResult->status = $this->determineTestResultStatus($flows);
        $testResult->save();
    }

    /**
     * Check if test result has timed out (>1 minute old)
     */
    private function isTimeout(TestResult $testResult): bool
    {
        return $testResult->status === TestResultStatus::PENDING
            && $testResult->created_at->diffInMinutes(now()) > 1;
    }

    /**
     * Determine the overall status of a test result based on its message flows
     */
    private function determineTestResultStatus(Collection $flows): TestResultStatus
    {
        if ($flows->contains(fn($flow) => $flow->status === TestResultStatus::FAILURE)) {
            return TestResultStatus::FAILURE;
        }
        
        if ($flows->contains(fn($flow) => $flow->status === TestResultStatus::TIMEOUT)) {
            return TestResultStatus::TIMEOUT;
        }
        
        if ($flows->every(fn($flow) => $flow->status === TestResultStatus::SUCCESS)) {
            return TestResultStatus::SUCCESS;
        }
        
        return TestResultStatus::PENDING;
    }
}
