<?php

namespace App\Services\Monitoring;

use App\Models\DeviceMonitoringResult;
use App\Models\TestScenario;
use App\Models\TestResult;
use Illuminate\Support\Facades\Log;

class TestExecutionService
{
    public function __construct(
        private readonly DeviceMonitoringService $monitoringService
    ) {}

    public function executeTest(TestScenario $scenario): TestResult
    {
        if (!$scenario->mqttDevice) {
            throw new \InvalidArgumentException("MQTT device not configured for test scenario");
        }

        Log::info("Executing test scenario: {$scenario->name}", [
            'mqtt_device_id' => $scenario->mqttDevice->id,
            'http_device_id' => $scenario->httpDevice?->id,
            'flows' => $scenario->httpDevice ? 'MQTT + HTTP (1-9)' : 'MQTT only (1-7)',
        ]);

        try {
            // Execute MQTT flows (1-7)
            $mqttResults = [
                $this->executeMqttFlow1($scenario),
                $this->executeMqttFlow2($scenario),
                $this->executeMqttFlow3($scenario),
                $this->executeMqttFlow4($scenario),
                $this->executeMqttFlow5($scenario),
                $this->executeMqttFlow6($scenario),
                $this->executeMqttFlow7($scenario),
            ];

            $results = $mqttResults;

            // If HTTP device is present, execute HTTP flows (8-9)
            if ($scenario->httpDevice) {
                $httpResults = [
                    $this->executeHttpFlow8($scenario),
                    $this->executeHttpFlow9($scenario),
                ];
                $results = array_merge($mqttResults, $httpResults);
            }

            // Analyze results and create test result record
            $failedResults = array_filter($results, fn($r) => !$r->success);
            $status = empty($failedResults) ? TestResult::STATUS_SUCCESS : TestResult::STATUS_FAILURE;
            $errorMessage = empty($failedResults) ? null : $this->formatErrorMessages($failedResults);

            return TestResult::create([
                'test_scenario_id' => $scenario->id,
                'flow_type' => $this->determineFlowType($scenario),
                'status' => $status,
                'error_message' => $errorMessage,
                'start_time' => now(),
                'end_time' => now(),
                'execution_time_ms' => collect($results)->avg('response_time_ms'),
                'service_type' => $failedResults[0]->service_type ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("Test execution failed: {$e->getMessage()}", [
                'scenario_id' => $scenario->id,
                'exception' => $e,
            ]);

            return TestResult::create([
                'test_scenario_id' => $scenario->id,
                'flow_type' => $this->determineFlowType($scenario),
                'status' => TestResult::STATUS_FAILURE,
                'error_message' => $e->getMessage(),
                'start_time' => now(),
                'end_time' => now(),
                'execution_time_ms' => 0,
            ]);
        }
    }

    private function determineFlowType(TestScenario $scenario): string
    {
        // Map flow numbers to flow types
        $flowMap = [
            1 => TestResult::FLOW_FULL_ROUTE_1,
            2 => TestResult::FLOW_ONE_WAY_ROUTE,
            3 => TestResult::FLOW_TWO_WAY_ROUTE,
            4 => TestResult::FLOW_DIRECT_TEST_1,
            5 => TestResult::FLOW_DIRECT_TEST_2,
            6 => TestResult::FLOW_TB_MQTT_HEALTH,
            7 => TestResult::FLOW_CS_MQTT_HEALTH,
            8 => TestResult::FLOW_TB_HTTP_HEALTH,
            9 => TestResult::FLOW_CS_HTTP_HEALTH,
        ];

        // Determine which flows to run based on device configuration
        if (!$scenario->httpDevice) {
            // MQTT only - flows 1-7
            return $flowMap[$scenario->flow_number] ?? $flowMap[1];
        }

        // Both MQTT and HTTP - all flows
        return $flowMap[$scenario->flow_number] ?? $flowMap[1];
    }

    private function formatErrorMessages(array $failedResults): string
    {
        return collect($failedResults)
            ->map(fn($r) => "Flow {$r->flow_number}: {$r->error_message}")
            ->join("\n");
    }

    private function executeMqttFlow(TestScenario $scenario, int $flowNumber): DeviceMonitoringResult
    {
        $device = $scenario->mqttDevice;

        // Create LPP payload
        $lppPayload = $this->createLppPayload(
            $flowNumber,
            $this->getNextCounter(),
            time()
        );

        // Send MQTT message with LPP payload
        $result = $this->monitoringService->checkMqttStatus(
            $device,
            'telemetry',
            [
                'data' => base64_encode($lppPayload),
                'fPort' => 1,
            ]
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'flow_number' => $flowNumber,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
            ],
        ]);
    }

    private function executeMqttFlow1(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 1: Send JSON, receive LPP
            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'f001digitalinput1' => 1,
                    'f001unsigned4b2' => $this->getNextCounter(),
                    'f001unsigned4b3' => time(),
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 1,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'json->lpp',
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 1',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow2(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 2: Send LPP, receive JSON
            $lppPayload = $this->createLppPayload(2, $this->getNextCounter(), time());

            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'data' => base64_encode($lppPayload),
                    'fPort' => 1,
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 2,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'lpp->json',
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 2',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow3(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 3: Send LPP, receive LPP
            $lppPayload = $this->createLppPayload(3, $this->getNextCounter(), time());

            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'data' => base64_encode($lppPayload),
                    'fPort' => 1,
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 3,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'lpp->lpp',
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 3',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow4(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 4: Send LPP, receive JSON
            $lppPayload = $this->createLppPayload(4, $this->getNextCounter(), time());

            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'data' => base64_encode($lppPayload),
                    'fPort' => 1,
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 4,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'lpp->json',
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 4',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow5(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 5: Send JSON, receive LPP
            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'f001digitalinput1' => 5,
                    'f001unsigned4b2' => $this->getNextCounter(),
                    'f001unsigned4b3' => time(),
                ]
            );

            $monitoringResult = DeviceMonitoringResult::create([
                'device_id' => $device->id,
                'test_scenario_id' => $scenario->id,
                'success' => $result['success'],
                'error_message' => $result['error_message'] ?? null,
                'response_time_ms' => $result['response_time_ms'] ?? 0,
                'metadata' => [
                    'flow_number' => 5,
                    'counter' => $result['counter'] ?? 0,
                    'timestamp' => $result['timestamp'] ?? 0,
                    'format' => 'json->lpp',
                ],
            ]);

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 5',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow6(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 6: Send JSON, receive JSON
            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'f001digitalinput1' => 6,
                    'f001unsigned4b2' => $this->getNextCounter(),
                    'f001unsigned4b3' => time(),
                ]
            );

            $monitoringResult = DeviceMonitoringResult::create([
                'device_id' => $device->id,
                'test_scenario_id' => $scenario->id,
                'success' => $result['success'],
                'error_message' => $result['error_message'] ?? null,
                'response_time_ms' => $result['response_time_ms'] ?? 0,
                'metadata' => [
                    'flow_number' => 6,
                    'counter' => $result['counter'] ?? 0,
                    'timestamp' => $result['timestamp'] ?? 0,
                    'format' => 'json->json',
                ],
            ]);

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 6',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeMqttFlow7(TestScenario $scenario): DeviceMonitoringResult 
    {
        try {
            $device = $scenario->mqttDevice;

            // Flow 7: Send LPP, receive LPP
            $lppPayload = $this->createLppPayload(7, $this->getNextCounter(), time());

            $result = $this->monitoringService->checkMqttStatus(
                $device,
                'telemetry',
                [
                    'data' => base64_encode($lppPayload),
                    'fPort' => 1,
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 7,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'lpp->lpp',
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing MQTT Flow 7',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->mqttDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeHttpFlow(TestScenario $scenario, int $flowNumber): DeviceMonitoringResult
    {
        $device = $scenario->httpDevice;

        // Send HTTP request with same LPP format
        $result = $this->monitoringService->checkHttpStatus(
            $device,
            'telemetry',
            [
                'f001digitalinput1' => $flowNumber,
                'f001unsigned4b2' => $this->getNextCounter(),
                'f001unsigned4b3' => time(),
            ]
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'flow_number' => $flowNumber,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'status_code' => $result['status_code'] ?? 0,
            ],
        ]);
    }

    private function executeHttpFlow8(TestScenario $scenario): DeviceMonitoringResult
    {
        try {
            if (!$scenario->httpDevice) {
                throw new \InvalidArgumentException("HTTP device not configured for test scenario");
            }

            $device = $scenario->httpDevice;

            // Flow 8: Send JSON, receive JSON
            $result = $this->monitoringService->checkHttpStatus(
                $device,
                'telemetry',
                [
                    'f001digitalinput1' => 8,
                    'f001unsigned4b2' => $this->getNextCounter(),
                    'f001unsigned4b3' => time(),
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 8,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'json->json',
                'status_code' => $result['status_code'] ?? 0,
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing HTTP Flow 8',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->httpDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function executeHttpFlow9(TestScenario $scenario): DeviceMonitoringResult
    {
        try {
            if (!$scenario->httpDevice) {
                throw new \InvalidArgumentException("HTTP device not configured for test scenario");
            }

            $device = $scenario->httpDevice;

            // Flow 9: Send LPP, receive LPP
            $lppPayload = $this->createLppPayload(9, $this->getNextCounter(), time());

            $result = $this->monitoringService->checkHttpStatus(
                $device,
                'telemetry',
                [
                    'data' => base64_encode($lppPayload),
                    'fPort' => 1,
                ]
            );

            $monitoringResult = new DeviceMonitoringResult();
            $monitoringResult->device_id = $device->id;
            $monitoringResult->test_scenario_id = $scenario->id;
            $monitoringResult->success = $result['success'];
            $monitoringResult->error_message = $result['error_message'] ?? null;
            $monitoringResult->response_time_ms = $result['response_time_ms'] ?? 0;
            $monitoringResult->metadata = [
                'flow_number' => 9,
                'counter' => $result['counter'] ?? 0,
                'timestamp' => $result['timestamp'] ?? 0,
                'format' => 'lpp->lpp',
                'status_code' => $result['status_code'] ?? 0,
            ];
            $monitoringResult->save();

            return $monitoringResult;
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error executing HTTP Flow 9',
                [
                    'error' => $e->getMessage(),
                    'device_id' => $scenario->httpDevice->id ?? null,
                    'test_scenario_id' => $scenario->id,
                    'trace' => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }
    }

    private function createLppPayload(int $flowType, int $counter, int $timestamp): string
    {
        $buffer = '';

        // Channel 1: flow_type (1 byte)
        $buffer .= chr(1);                    // Channel
        $buffer .= chr(0x00);                 // Type (Digital Input)
        $buffer .= chr($flowType);            // Value

        // Channel 2: counter (4 bytes)
        $buffer .= chr(2);                    // Channel
        $buffer .= chr(0xfe);                 // Type (Unsigned 4B)
        $buffer .= pack('N', $counter);       // Value (big-endian)

        // Channel 3: timestamp (4 bytes)
        $buffer .= chr(3);                    // Channel
        $buffer .= chr(0xfe);                 // Type (Unsigned 4B)
        $buffer .= pack('N', $timestamp);     // Value (big-endian)

        return $buffer;
    }

    private function getNextCounter(): int
    {
        static $counter = 0;
        return ++$counter;
    }
}
