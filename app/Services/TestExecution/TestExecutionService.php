<?php

namespace App\Services\TestExecution;

use App\Models\TestResult;
use App\Models\TestScenario;
use App\Services\ChirpStack\ChirpStackService;
use App\Services\Mqtt\MqttMonitor;
use App\Services\ThingsBoard\ThingsBoardService;
use App\Enums\FlowType;
use App\Enums\TestResultStatus;
use App\Enums\ServiceType;
use App\Services\Device\DeviceCommunicationService;
use Exception;
use Illuminate\Support\Facades\Log;

class TestExecutionService
{
    public function __construct(
        protected ChirpStackService $chirpStackService,
        protected ThingsBoardService $thingsBoardService,
        protected MqttMonitor $mqttService,
        protected DeviceCommunicationService $deviceCommunicationService,
    ) {}

    public function executeScenario(TestScenario $scenario): void
    {
        // Skip if scenario is not active
        if (! $scenario->is_active) {
            return;
        }

        try {
            // Execute all test flows for this scenario
            $this->executeThingsBoardToChirpStack($scenario);
            $this->executeChirpStackToThingsBoard($scenario);
            $this->executeThingsBoardToChirpStackToThingsBoard($scenario);
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

    /**
     * Flow 1: ThingsBoard -> ChirpStack
     * 
     * Flow:
     * 1. ThingsBoard sends JSON via HTTP
     * 2. Our App receives via HTTP
     * 3. Our App converts to LPP
     * 4. Our App sends to ChirpStack
     * 
     * Status: Only set PENDING, let webhook handle the rest
     */
    protected function executeThingsBoardToChirpStack(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::TB_TO_CS,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            $this->deviceCommunicationService->sendThingsBoardCommand(
                $scenario->httpDevice,
                [
                    'flowNumber' => 1,
                    'testResultId' => $result->id,
                    'timestamp' => time(),
                ]
            );
        } catch (Exception $e) {
            // Just log the error, don't change status
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    /**
     * Flow 2: ChirpStack -> ThingsBoard
     * 
     * Flow:
     * 1. Our App creates LPP data
     * 2. Our App sends to ChirpStack via MQTT
     * 3. ChirpStack processes and sends back
     * 4. Our App receives via webhook
     * 5. Our App converts to JSON
     * 6. Our App sends to ThingsBoard
     * 
     * Status: Only set PENDING, let webhook handle the rest
     */
    protected function executeChirpStackToThingsBoard(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::CS_TO_TB,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            $this->deviceCommunicationService->sendChirpStackMessage(
                $scenario->mqttDevice,
                2, // Flow 2: CS -> TB
                $result->id
            );
        } catch (Exception $e) {
            // Just log the error, don't change status
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    /**
     * Flow 3: ThingsBoard -> ChirpStack -> ThingsBoard
     * 
     * Flow:
     * 1. ThingsBoard sends JSON via HTTP
     * 2. Our App receives via HTTP
     * 3. Our App converts to LPP
     * 4. Our App sends to ChirpStack
     * 5. ChirpStack processes and sends back
     * 6. Our App receives via webhook
     * 7. Our App converts to JSON
     * 8. Our App sends to ThingsBoard
     * 
     * Status: Only set PENDING, let webhook handle the rest
     */
    protected function executeThingsBoardToChirpStackToThingsBoard(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::TB_TO_CS_TO_TB,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            $this->deviceCommunicationService->sendThingsBoardCommand(
                $scenario->httpDevice,
                [
                    'flowNumber' => 3,
                    'testResultId' => $result->id,
                    'timestamp' => time(),
                ]
            );
        } catch (Exception $e) {
            // Just log the error, don't change status
            $result->error_message = $e->getMessage();
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeTbMqttHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::TB_MQTT_HEALTH,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            // Test MQTT connection to ThingsBoard
            $success = $this->deviceCommunicationService->checkThingsBoardMqttHealth(
                $scenario->mqttDevice
            );

            if (!$success) {
                throw new Exception('Failed to connect to ThingsBoard MQTT');
            }

            $result->status = TestResultStatus::SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResultStatus::FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = ServiceType::THINGSBOARD;
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeCsMqttHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::CS_MQTT_HEALTH,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            // Test MQTT connection to ChirpStack
            $success = $this->deviceCommunicationService->checkChirpStackMqttHealth(
                $scenario->mqttDevice
            );

            if (!$success) {
                throw new Exception('Failed to connect to ChirpStack MQTT');
            }

            $result->status = TestResultStatus::SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResultStatus::FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = ServiceType::CHIRPSTACK;
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeTbHttpHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::TB_HTTP_HEALTH,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            // Test HTTP connection to ThingsBoard
            $success = $this->deviceCommunicationService->checkThingsBoardHttpHealth(
                $scenario->httpDevice
            );

            if (!$success) {
                throw new Exception('Failed to connect to ThingsBoard HTTP');
            }

            $result->status = TestResultStatus::SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResultStatus::FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = ServiceType::THINGSBOARD;
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function executeCsHttpHealth(TestScenario $scenario): void
    {
        $result = new TestResult([
            'test_scenario_id' => $scenario->id,
            'flow_type' => FlowType::CS_HTTP_HEALTH,
            'start_time' => now(),
            'status' => TestResultStatus::PENDING,
        ]);

        try {
            // Test HTTP connection to ChirpStack
            $success = $this->deviceCommunicationService->checkChirpStackHttpHealth(
                $scenario->httpDevice
            );

            if (!$success) {
                throw new Exception('Failed to connect to ChirpStack HTTP');
            }

            $result->status = TestResultStatus::SUCCESS;
        } catch (Exception $e) {
            $result->status = TestResultStatus::FAILURE;
            $result->error_message = $e->getMessage();
            $result->service_type = ServiceType::CHIRPSTACK;
        }

        $result->end_time = now();
        $result->execution_time_ms = (int) $result->end_time->diffInMilliseconds($result->start_time);
        $scenario->results()->save($result);
    }

    protected function determineFailedService(string $errorMessage): ServiceType
    {
        if (str_contains($errorMessage, 'ThingsBoard')) {
            return ServiceType::THINGSBOARD;
        }
        if (str_contains($errorMessage, 'MQTT')) {
            if (str_contains($errorMessage, 'ThingsBoard')) {
                return ServiceType::MQTT_TB;
            }
            return ServiceType::MQTT_CS;
        }
        if (str_contains($errorMessage, 'ChirpStack')) {
            return ServiceType::CHIRPSTACK;
        }

        return ServiceType::CHIRPSTACK; // Default to ChirpStack as it's the most common failure point
    }

    /**
     * Create LPP data for flow validation
     */
    private function createLppData(int $flowNumber, int $testResultId, int $timestamp): string
    {
        $data = '';
        
        // Channel 1: Flow number (digital input)
        $data .= chr(1); // Channel
        $data .= chr(0); // Digital Input type
        $data .= chr($flowNumber);
        
        // Channel 2: Test result ID (unsigned 4B)
        $data .= chr(2); // Channel
        $data .= chr(0xFE); // Unsigned 4B type
        $data .= pack('N', $testResultId);
        
        // Channel 3: Timestamp (unsigned 4B)
        $data .= chr(3); // Channel
        $data .= chr(0xFE); // Unsigned 4B type
        $data .= pack('N', $timestamp);
        
        return base64_encode($data);
    }
}
