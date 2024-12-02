<?php

namespace App\Services\Monitoring;

use App\Models\TestResult;
use App\Models\TestScenario;
use App\Models\TestScenarioServiceStatus;
use App\Enums\ServiceType;
use App\Enums\StatusType;
use App\Enums\TestResultStatus;
use Carbon\Carbon;

class ServiceStatusService
{
    /**
     * Get or create service status for a test scenario and service type
     */
    public function getOrCreateForService(TestScenario $scenario, ServiceType $serviceType): TestScenarioServiceStatus
    {
        return TestScenarioServiceStatus::firstOrCreate([
            'test_scenario_id' => $scenario->id,
            'service_type' => $serviceType->value,
        ], [
            'status' => StatusType::HEALTHY,
            'success_count_1h' => 0,
            'total_count_1h' => 0,
            'success_rate_1h' => 100,
        ]);
    }

    /**
     * Handle a new test result
     */
    public function handleTestResult(TestResult $result): void
    {
        if (!$result->service_type) {
            return; // Skip if no service type specified
        }

        $serviceType = ServiceType::from($result->service_type);
        $status = $this->getOrCreateForService($result->testScenario, $serviceType);
        
        $success = $result->status === TestResultStatus::SUCCESS;
        $this->updateStatus($status, $success);
    }

    /**
     * Update service status based on a test result
     */
    public function updateStatus(TestScenarioServiceStatus $status, bool $success): void
    {
        // Update counters
        $this->updateCounters($status, $success);

        // Calculate success rate
        $this->calculateSuccessRate($status);

        // Update timestamps
        $status->last_check_at = now();
        
        if ($success) {
            $status->last_success_at = now();
            $status->downtime_started_at = null;  // Clear downtime on success
        } else {
            $status->last_failure_at = now();
            // Set downtime_started_at only if it's not already set
            if (!$status->downtime_started_at) {
                $status->downtime_started_at = now();
            }
        }

        // Determine status based on success rate and downtime
        $this->determineStatus($status);

        $status->save();
    }

    /**
     * Update success/failure counters for the last hour
     */
    protected function updateCounters(TestScenarioServiceStatus $status, bool $success): void
    {
        // Get results from the last hour
        $oneHourAgo = Carbon::now()->subHour();

        // Reset counters if no recent checks
        if ($status->last_check_at === null || $status->last_check_at->lt($oneHourAgo)) {
            $status->success_count_1h = $success ? 1 : 0;
            $status->total_count_1h = 1;
            return;
        }

        // Update counters
        $status->total_count_1h++;
        if ($success) {
            $status->success_count_1h++;
        }
    }

    /**
     * Calculate success rate based on counters
     */
    protected function calculateSuccessRate(TestScenarioServiceStatus $status): void
    {
        if ($status->total_count_1h > 0) {
            $status->success_rate_1h = round(($status->success_count_1h / $status->total_count_1h) * 100);
        } else {
            $status->success_rate_1h = 100; // Default to 100% if no checks
        }
    }

    /**
     * Determine service status based on metrics
     */
    protected function determineStatus(TestScenarioServiceStatus $status): void
    {
        // Check if service has been down for configured minutes
        if ($status->downtime_started_at && 
            Carbon::parse($status->downtime_started_at)->diffInMinutes(now()) >= config('monitoring.status.critical_downtime_minutes')) {
            $status->status = StatusType::CRITICAL;
            return;
        }

        // Check if success rate is below configured threshold
        if ($status->success_rate_1h < config('monitoring.status.warning_success_rate')) {
            $status->status = StatusType::WARNING;
            return;
        }

        // Otherwise healthy
        $status->status = StatusType::HEALTHY;
    }
}
