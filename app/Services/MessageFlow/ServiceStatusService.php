<?php

namespace App\Services\MessageFlow;

use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Enums\StatusType;
use App\Enums\TestResultStatus;
use App\Models\TestResult;
use App\Models\TestScenario;
use App\Models\TestScenarioServiceStatus;
use App\Models\MessageFlow;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ServiceStatusService
{
    private ServiceFailureAnalyzer $failureAnalyzer;
    private NotificationService $notificationService;

    public function __construct(
        ServiceFailureAnalyzer $failureAnalyzer,
        NotificationService $notificationService
    ) {
        $this->failureAnalyzer = $failureAnalyzer;
        $this->notificationService = $notificationService;
    }

    /**
     * Update service metrics based on completed test result
     */
    public function updateServiceMetrics(TestResult $testResult): void
    {
        try {
            // Only process if test result is complete (not pending)
            if ($testResult->status === TestResultStatus::PENDING) {
                return;
            }

            // Get all message flows
            $flows = $testResult->messageFlows;
            if ($flows->isEmpty()) {
                Log::warning('No message flows found for test result', ['test_result_id' => $testResult->id]);
                return;
            }

            // Update test result metrics
            $this->updateTestResultMetrics($testResult, $flows);

            // Update service status metrics
            $this->updateServiceStatusMetrics($testResult->testScenario, $flows);

            // Update message flow metrics
            $this->updateMessageFlowMetrics($flows);

        } catch (\Exception $e) {
            Log::error('Error updating service metrics', [
                'test_result_id' => $testResult->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update metrics in test_results table
     */
    private function updateTestResultMetrics(TestResult $testResult, Collection $flows): void
    {
        try {
            // Calculate total execution time from message flows
            $maxResponseTime = $flows->max('response_time_ms') ?? 0;
            
            // Determine overall status based on flows
            $status = $this->determineTestResultStatus($flows);
            
            // Find failed service if status is FAILURE
            $failedService = null;
            if ($status === TestResultStatus::FAILURE) {
                $failedServices = $this->failureAnalyzer->analyzePotentialFailures($flows);
                $failedService = $failedServices->first();
            }
            
            // Update test result
            $testResult->update([
                'status' => $status,
                'end_time' => now(),
                'execution_time_ms' => $maxResponseTime,
                'service_type' => $failedService?->value,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating test result metrics', [
                'test_result_id' => $testResult->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Determine the overall status of a test result based on its message flows
     */
    private function determineTestResultStatus(Collection $flows): TestResultStatus
    {
        // If any flow failed, the test result is a failure
        if ($flows->contains(fn($flow) => $flow->status === TestResultStatus::FAILURE)) {
            return TestResultStatus::FAILURE;
        }
        
        // If any flow timed out, the test result is a timeout
        if ($flows->contains(fn($flow) => $flow->status === TestResultStatus::TIMEOUT)) {
            return TestResultStatus::TIMEOUT;
        }
        
        // If all flows succeeded, the test result is a success
        if ($flows->every(fn($flow) => $flow->status === TestResultStatus::SUCCESS)) {
            return TestResultStatus::SUCCESS;
        }
        
        // If we still have any pending flows, the test result is pending
        return TestResultStatus::PENDING;
    }

    /**
     * Update metrics in test_scenario_service_status table
     */
    private function updateServiceStatusMetrics(TestScenario $scenario, Collection $flows): void
    {
        try {
            // Get affected services from flows
            $affectedServices = $this->failureAnalyzer->analyzePotentialFailures($flows);
            
            foreach ($affectedServices as $serviceType) {
                // Get or create service status
                $status = $this->getOrCreateServiceStatus($scenario, $serviceType);
                
                // Update counters
                $status->total_count_1h++;
                
                // Check if service failed in this test
                $serviceFailed = $flows->contains(function ($flow) {
                    return $flow->status === TestResultStatus::FAILURE || 
                           $flow->status === TestResultStatus::TIMEOUT;
                });
                
                if ($serviceFailed) {
                    $status->last_failure_at = now();
                    if (!$status->downtime_started_at) {
                        $status->downtime_started_at = now();
                    }
                } else {
                    $status->success_count_1h++;
                    $status->last_success_at = now();
                    $status->downtime_started_at = null;
                }
                
                // Calculate success rate
                $status->success_rate_1h = round(($status->success_count_1h / $status->total_count_1h) * 100, 2);
                
                // Determine new status
                $this->determineServiceStatus($status);
                
                $status->save();
            }
        } catch (\Exception $e) {
            Log::error('Error updating service status metrics', [
                'test_scenario_id' => $scenario->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get or create service status for a test scenario and service
     */
    private function getOrCreateServiceStatus(TestScenario $scenario, ServiceType $serviceType): TestScenarioServiceStatus
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
     * Determine service status based on metrics and thresholds
     */
    private function determineServiceStatus(TestScenarioServiceStatus $status): void
    {
        $oldStatus = $status->status;

        // Check if service has been down for configured minutes
        if ($status->downtime_started_at && 
            Carbon::parse($status->downtime_started_at)->diffInMinutes(now()) >= config('monitoring.status.critical_downtime_minutes', 10)) {
            $status->status = StatusType::CRITICAL;
            
            // Only notify if status changed to CRITICAL
            if ($oldStatus !== StatusType::CRITICAL) {
                // Get test scenario notifications
                $notifications = $status->testScenario->notifications;
                foreach ($notifications as $notification) {
                    $this->notificationService->sendNotification($notification, $status->lastResult);
                }
            }
            return;
        }

        // Check if success rate is below configured threshold
        if ($status->success_rate_1h < config('monitoring.status.warning_success_rate', 90)) {
            $status->status = StatusType::WARNING;
            return;
        }

        // Otherwise healthy
        $status->status = StatusType::HEALTHY;
    }

    /**
     * Update metrics in message_flows table
     */
    private function updateMessageFlowMetrics(Collection $flows): void
    {
        try {
            foreach ($flows as $flow) {
                // Skip if flow is already completed
                if ($flow->isCompleted()) {
                    continue;
                }

                // Check for timeout
                if ($flow->started_at && 
                    Carbon::parse($flow->started_at)->diffInMinutes(now()) > 1) {
                    $flow->update([
                        'status' => TestResultStatus::TIMEOUT,
                        'completed_at' => now(),
                        'response_time_ms' => Carbon::parse($flow->started_at)->diffInMilliseconds(now())
                    ]);
                    continue;
                }

                // Update any remaining pending flows
                if ($flow->status === TestResultStatus::PENDING) {
                    $flow->update([
                        'completed_at' => now(),
                        'response_time_ms' => $flow->started_at ? 
                            Carbon::parse($flow->started_at)->diffInMilliseconds(now()) : 
                            0
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating message flow metrics', [
                'flow_ids' => $flows->pluck('id'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
