<?php

namespace App\Services\MessageFlow;

use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Enums\ServiceStatus;
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

    const MINIMUM_REQUIRED_FLOWS = 7;

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
            info("Starting metrics update pipeline for TestResult ID: " . $testResult->id . " with " . $flows->count() . " flows");
            
            info("1. Updating test result metrics");
            $this->updateTestResultMetrics($testResult, $flows);
            
            info("2. Updating service status metrics for TestScenario ID: " . $testResult->testScenario->id);
            $this->updateServiceStatusMetrics($testResult->testScenario, $flows);
            
            info("3. Updating message flow metrics");
            $this->updateMessageFlowMetrics($flows);
            
            info("Completed metrics update pipeline for TestResult ID: " . $testResult->id);

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
            info("Calculating metrics for TestResult ID: " . $testResult->id);
            
            // Calculate total execution time from message flows
            $maxResponseTime = $flows->max('response_time_ms') ?? 0;
            info("Max response time for TestResult ID: " . $testResult->id . " is " . $maxResponseTime . "ms");
            
            // Determine overall status based on flows
            $status = $this->determineTestResultStatus($flows);
            info("Determined status for TestResult ID: " . $testResult->id . " is " . $status->value);
            
            // Find failed service if status is FAILURE
            $failedService = null;
            if ($status === TestResultStatus::FAILURE) {
                $failedServices = $this->failureAnalyzer->analyzePotentialFailures($flows);
                $failedService = $failedServices->first();
                info("Failed service for TestResult ID: " . $testResult->id . " is " . ($failedService ? $failedService->value : 'none'));
            }
            
            // Update test result
            info("Updating TestResult ID: " . $testResult->id . " with status: " . $status->value . ", execution_time: " . $maxResponseTime . "ms");
            $testResult->update([
                'status' => $status,
                'end_time' => now(),
                'execution_time_ms' => $maxResponseTime,
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
               
        
        // If all flows succeeded, the test result is a success
        if ($flows->every(fn($flow) => $flow->status === TestResultStatus::SUCCESS)) {
            return TestResultStatus::SUCCESS;
        }
        
        // If we still have any pending flows, the test result is pending
        return TestResultStatus::PENDING;
    }

    /**
     * Update metrics for a test scenario and its flows
     */
    public function updateServiceStatusMetrics(TestScenario $scenario, Collection $flows): void
    {
        try {
            info("Starting service status metrics update for TestScenario ID: " . $scenario->id);
            
            // Minimum required flows for valid service status            
            
            // Update metrics for ALL services
            foreach (ServiceType::cases() as $service) {
                $serviceType = $service->value;
                info("Processing service: " . $serviceType);

                // Get or create service status
                $status = $this->getOrCreateServiceStatus($scenario, $service);
                
                // Filter flows that are defined in the matrix
                $validFlows = $flows->filter(function($flow) use ($serviceType) {
                    return isset(ServiceFailureAnalyzer::SERVICE_MATRIX_FLOW_FAIL[$serviceType][$flow->flow_type->value]);
                });
                
                // First check if we have enough valid flows
                if ($validFlows->count() < self::MINIMUM_REQUIRED_FLOWS) {
                    info("Not enough valid flows for service {$serviceType}. Required: " . self::MINIMUM_REQUIRED_FLOWS . ", Got: " . $validFlows->count());
                    continue;
                }
                
                // Check if flows match their expected state in the matrix
                $serviceFailed = false;
                $allFlowsMatchExpectedState = true;
                
                foreach ($validFlows as $flow) {
                    $mustFail = ServiceFailureAnalyzer::SERVICE_MATRIX_FLOW_FAIL[$serviceType][$flow->flow_type->value];
                    $isFailed = $flow->status === TestResultStatus::FAILURE;
                    
                    // If the flow state doesn't match what we expect, service is not in failed state
                    if ($mustFail !== $isFailed) {
                        $allFlowsMatchExpectedState = false;
                        break;
                    }
                }
                
                $serviceFailed = $allFlowsMatchExpectedState;

                // Update counters and check failures
                $status->total_count_1h++;
                if ($serviceFailed) {
                    $status->last_failure_at = now();
                    if (!$status->downtime_started_at) {
                        $status->downtime_started_at = now();
                        info("Starting downtime for service: " . $serviceType);
                    }
                } else {
                    $status->success_count_1h++;
                    $status->last_success_at = now();
                    if ($status->downtime_started_at) {
                        info("Clearing downtime for service: " . $serviceType);
                    }
                    $status->downtime_started_at = null;
                }

                // Calculate success rate
                $status->success_rate_1h = round(($status->success_count_1h / $status->total_count_1h) * 100, 2);
                info("Updated metrics for service " . $serviceType . ": success_rate=" . $status->success_rate_1h . "%, total_count=" . $status->total_count_1h);
                
                // Update TestScenario metrics for this service
                $serviceField = strtolower($serviceType);
                $scenario->{"${serviceField}_success_rate_1h"} = $status->success_rate_1h;
                $scenario->{"${serviceField}_messages_count_1h"} = $status->total_count_1h;
                $scenario->{"${serviceField}_last_success_at"} = $status->last_success_at;
                $scenario->{"${serviceField}_status"} = $status->status;
                $scenario->save();
                
                // Determine new status
                $oldStatus = $status->status;
                $status->status = $this->determineServiceStatus($status);
                
                if ($status->isDirty('status')) {
                    info("Service " . $serviceType . " status changed to " . $status->status->value);
                }
                
                $status->save();
            }
        } catch (\Exception $e) {
            error_log("Error updating service status metrics: " . $e->getMessage());
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
    private function determineServiceStatus(TestScenarioServiceStatus $status): StatusType
    {
        // Check if service has been down for configured minutes
        if ($status->downtime_started_at && 
            Carbon::parse($status->downtime_started_at)->diffInMinutes(now()) >= config('monitoring.status.critical_downtime_minutes', 10)) {
            return StatusType::CRITICAL;
        }

        // Check if success rate is below configured threshold
        if ($status->success_rate_1h < config('monitoring.status.warning_success_rate', 90)) {
            return StatusType::WARNING;
        }

        // Otherwise healthy
        return StatusType::HEALTHY;
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
                        'status' => TestResultStatus::FAILURE,
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

    /**
     * Update metrics from test result
     */
    public function updateMetricsFromTestResult(TestResult $testResult): void
    {
        $this->updateServiceStatusMetrics($testResult->testScenario, $testResult->messageFlows);
    }
}
