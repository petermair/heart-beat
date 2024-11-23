<?php

namespace App\Services\Monitoring;

use App\Models\DeviceMonitoringResult;
use App\Models\TestScenario;
use Illuminate\Support\Facades\Log;

class TestExecutionService
{
    public function __construct(
        private readonly DeviceMonitoringService $monitoringService
    ) {}

    public function executeTest(TestScenario $scenario): DeviceMonitoringResult
    {
        Log::info("Executing test scenario: {$scenario->name}", [
            'test_type' => $scenario->test_type,
            'device_id' => $scenario->device_id,
        ]);

        try {
            $result = match ($scenario->test_type) {
                'mqtt_rx' => $this->executeMqttRxTest($scenario),
                'mqtt_tx' => $this->executeMqttTxTest($scenario),
                'http_health' => $this->executeHttpHealthTest($scenario),
                'http_telemetry' => $this->executeHttpTelemetryTest($scenario),
                'http_rpc' => $this->executeHttpRpcTest($scenario),
                default => throw new \InvalidArgumentException("Unknown test type: {$scenario->test_type}"),
            };

            return $result;
        } catch (\Exception $e) {
            Log::error("Test execution failed: {$e->getMessage()}", [
                'test_type' => $scenario->test_type,
                'device_id' => $scenario->device_id,
                'exception' => $e,
            ]);

            // Create a failed result
            return DeviceMonitoringResult::create([
                'device_id' => $scenario->device_id,
                'test_scenario_id' => $scenario->id,
                'success' => false,
                'error_message' => $e->getMessage(),
                'response_time_ms' => 0,
                'metadata' => [
                    'test_type' => $scenario->test_type,
                    'error_type' => get_class($e),
                ],
            ]);
        }
    }

    private function executeMqttRxTest(TestScenario $scenario): DeviceMonitoringResult
    {
        $config = $scenario->test_configuration;
        $device = $scenario->device;

        $result = $this->monitoringService->checkMqttRxStatus(
            $device,
            $config['expected_message_count'] ?? 1,
            $config['message_timeout'] ?? $scenario->timeout_seconds
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'test_type' => 'mqtt_rx',
                'messages_received' => $result['messages_received'] ?? 0,
                'expected_messages' => $config['expected_message_count'] ?? 1,
            ],
        ]);
    }

    private function executeMqttTxTest(TestScenario $scenario): DeviceMonitoringResult
    {
        $config = $scenario->test_configuration;
        $device = $scenario->device;

        $result = $this->monitoringService->checkMqttTxStatus(
            $device,
            $config['message_payload'] ?? 'test',
            $config['expect_ack'] ?? true
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'test_type' => 'mqtt_tx',
                'ack_received' => $result['ack_received'] ?? false,
            ],
        ]);
    }

    private function executeHttpHealthTest(TestScenario $scenario): DeviceMonitoringResult
    {
        $config = $scenario->test_configuration;
        $device = $scenario->device;

        $result = $this->monitoringService->checkHttpStatus(
            $device,
            $config['expected_status'] ?? 200
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'test_type' => 'http_health',
                'status_code' => $result['status_code'] ?? 0,
            ],
        ]);
    }

    private function executeHttpTelemetryTest(TestScenario $scenario): DeviceMonitoringResult
    {
        $config = $scenario->test_configuration;
        $device = $scenario->device;

        $result = $this->monitoringService->checkTelemetryData(
            $device,
            $config['data_points'] ?? ['temperature', 'humidity']
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'test_type' => 'http_telemetry',
                'data_points_found' => $result['data_points_found'] ?? [],
            ],
        ]);
    }

    private function executeHttpRpcTest(TestScenario $scenario): DeviceMonitoringResult
    {
        $config = $scenario->test_configuration;
        $device = $scenario->device;

        $result = $this->monitoringService->checkRpcCall(
            $device,
            $config['method'] ?? 'getValue',
            $config['params'] ?? []
        );

        return DeviceMonitoringResult::create([
            'device_id' => $device->id,
            'test_scenario_id' => $scenario->id,
            'success' => $result['success'],
            'error_message' => $result['error_message'] ?? null,
            'response_time_ms' => $result['response_time_ms'] ?? 0,
            'metadata' => [
                'test_type' => 'http_rpc',
                'method' => $config['method'],
                'response' => $result['response'] ?? null,
            ],
        ]);
    }
}
