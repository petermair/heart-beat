<?php

namespace App\Services\TestExecution;

use App\Models\TestResult;
use App\Models\TestScenario;
use App\Services\ChirpStack\ChirpStackService;
use App\Services\Mqtt\MqttMonitor;
use App\Services\ThingsBoard\ThingsBoardService;
use Exception;
use Illuminate\Support\Facades\Log;

class TestExecutionService
{
    public function __construct(
        protected ChirpStackService $chirpStackService,
        protected ThingsBoardService $thingsBoardService,
        protected MqttMonitor $mqttService,
    ) {}

    public function executeScenario(TestScenario $scenario): void
    {
        // Skip if scenario is not active
        if (! $scenario->is_active) {
            return;
        }

        try {
            // Execute all test flows for this scenario
            $this->executeFullRoute1($scenario);
            $this->executeOneWayRoute($scenario);
            $this->executeTwoWayRoute($scenario);
            $this->executeDirectTest1($scenario);
            $this->executeDirectTest2($scenario);
            $this->executeTbMqttHealth($scenario);
            $this->executeCsMqttHealth($scenario);
            $this->executeTbHttpHealth($scenario);
            $this->executeCsHttpHealth($scenario);
        } catch (Exception $e) {
            Log::error('Failed to execute test scenario', [
                'scenario_id' => $scenario->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function executeFullRoute1(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_FULL_ROUTE_1,
            'start_time' => now(),
        ]);

        try {
            // Step 1: Send command from ThingsBoard to device
            $tbSuccess = $this->thingsBoardService->sendDeviceCommand(
                $scenario->httpDevice,
                ['command' => 'test_full_route']
            );
            if (! $tbSuccess) {
                throw new Exception('Failed to send command from ThingsBoard');
            }

            // Step 2: Verify MQTT message was received
            $mqttSuccess = $this->mqttService->waitForMessage(
                $scenario->mqttDevice,
                timeout: $scenario->timeout_seconds
            );
            if (! $mqttSuccess) {
                throw new Exception('Failed to receive MQTT message');
            }

            // Step 3: Verify message was forwarded to ChirpStack
            $csSuccess = $this->chirpStackService->waitForDeviceMessage(
                $scenario->mqttDevice,
                timeout: $scenario->timeout_seconds
            );
            if (! $csSuccess) {
                throw new Exception('Failed to receive message in ChirpStack');
            }

            // All steps succeeded
            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = $this->determineFailedService($e->getMessage());
        }

        // Complete and save the result
        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeOneWayRoute(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_ONE_WAY_ROUTE,
            'start_time' => now(),
        ]);

        try {
            // Step 1: Send uplink from ChirpStack
            $csSuccess = $this->chirpStackService->simulateDeviceUplink(
                $scenario->mqttDevice,
                ['data' => 'test_one_way']
            );
            if (! $csSuccess) {
                throw new Exception('Failed to send uplink from ChirpStack');
            }

            // Step 2: Verify MQTT message was received
            $mqttSuccess = $this->mqttService->waitForMessage(
                $scenario->mqttDevice,
                timeout: $scenario->timeout_seconds
            );
            if (! $mqttSuccess) {
                throw new Exception('Failed to receive MQTT message');
            }

            // Step 3: Verify message was forwarded to ThingsBoard
            $tbSuccess = $this->thingsBoardService->waitForTelemetry(
                $scenario->httpDevice,
                timeout: $scenario->timeout_seconds
            );
            if (! $tbSuccess) {
                throw new Exception('Failed to receive telemetry in ThingsBoard');
            }

            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = $this->determineFailedService($e->getMessage());
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeTwoWayRoute(TestScenario $scenario): void
    {
        // Similar to OneWayRoute but includes return path through FullRoute1
        // Implementation follows the same pattern
    }

    protected function executeDirectTest1(TestScenario $scenario): void
    {
        // Direct test from ChirpStack to ThingsBoard
        // Implementation follows the same pattern
    }

    protected function executeDirectTest2(TestScenario $scenario): void
    {
        // Direct test from ThingsBoard to ChirpStack
        // Implementation follows the same pattern
    }

    protected function executeTbMqttHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_TB_MQTT_HEALTH,
            'start_time' => now(),
            'service_type' => TestResult::SERVICE_THINGSBOARD,
        ]);

        try {
            // Test MQTT connection to ThingsBoard
            $success = $this->thingsBoardService->testMqttConnection($scenario->mqttDevice);
            if (! $success) {
                throw new Exception('Failed to connect to ThingsBoard via MQTT');
            }

            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeCsMqttHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_CS_MQTT_HEALTH,
            'start_time' => now(),
            'service_type' => TestResult::SERVICE_CHIRPSTACK,
        ]);

        try {
            // Test MQTT connection to ChirpStack
            $success = $this->chirpStackService->testMqttConnection($scenario->mqttDevice);
            if (! $success) {
                throw new Exception('Failed to connect to ChirpStack via MQTT');
            }

            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeTbHttpHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_TB_HTTP_HEALTH,
            'start_time' => now(),
            'service_type' => TestResult::SERVICE_THINGSBOARD,
        ]);

        try {
            // Test HTTP connection to ThingsBoard
            $success = $this->thingsBoardService->testHttpConnection($scenario->httpDevice);
            if (! $success) {
                throw new Exception('Failed to connect to ThingsBoard via HTTP');
            }

            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeCsHttpHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => TestResult::FLOW_CS_HTTP_HEALTH,
            'start_time' => now(),
            'service_type' => TestResult::SERVICE_CHIRPSTACK,
        ]);

        try {
            // Test HTTP connection to ChirpStack
            $success = $this->chirpStackService->testHttpConnection($scenario->httpDevice);
            if (! $success) {
                throw new Exception('Failed to connect to ChirpStack via HTTP');
            }

            $result->status = TestResult::STATUS_SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResult::STATUS_FAILURE;
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function determineFailedService(string $errorMessage): string
    {
        if (str_contains($errorMessage, 'ThingsBoard')) {
            return TestResult::SERVICE_THINGSBOARD;
        }
        if (str_contains($errorMessage, 'MQTT')) {
            return TestResult::SERVICE_MQTT;
        }
        if (str_contains($errorMessage, 'ChirpStack')) {
            return TestResult::SERVICE_CHIRPSTACK;
        }

        return TestResult::SERVICE_UNKNOWN;
    }
}
