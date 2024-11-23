<?php

namespace App\Services\Monitoring;

use App\Models\Device;
use App\Models\DeviceMonitoringResult;
use App\Services\ChirpStack\ChirpStackService;
use App\Services\ThingsBoard\ThingsBoardService;
use Illuminate\Support\Facades\Log;

class DeviceMonitoringService
{
    public function __construct(
        protected ChirpStackService $chirpStackService,
        protected ThingsBoardService $thingsBoardService
    ) {}

    /**
     * Monitor a specific device
     * @param Device $device The device to monitor
     * @param string $testType The type of test being performed
     * @return DeviceMonitoringResult The monitoring result
     */
    public function monitorDevice(Device $device, string $testType = 'scheduled'): DeviceMonitoringResult
    {
        if (!$device->is_active || !$device->monitoring_enabled) {
            return $this->createResult($device, false, 'Device is not active or monitoring is disabled', $testType);
        }

        try {
            return $this->checkDeviceStatus($device, $testType);
        } catch (\Exception $e) {
            Log::error('Device monitoring failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createResult($device, false, $e->getMessage(), $testType);
        }
    }

    /**
     * Check device status based on communication type
     * @param Device $device The device to check
     * @param string $testType The type of test being performed
     * @return DeviceMonitoringResult The monitoring result
     * @throws \InvalidArgumentException When communication type is not supported
     */
    public function checkDeviceStatus(Device $device, string $testType): DeviceMonitoringResult
    {
        $communicationType = $device->communicationType->name;

        return match ($communicationType) {
            'mqtt' => $this->checkMqttStatus($device, $testType),
            'http' => $this->checkHttpStatus($device, $testType),
            default => throw new \InvalidArgumentException("Unsupported communication type: {$communicationType}"),
        };
    }

    /**
     * Unified MQTT status check method
     * @param Device $device The device to check
     * @param string $type The type of check (rx, tx, telemetry, rpc)
     * @param array $options Additional options for the check
     * @return array The check result
     */
    public function checkMqttStatus(Device $device, string $testType, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $result = match ($testType) {
                'rx' => $this->chirpStackService->checkDeviceRxMessages(
                    $device->chirpstackServer,
                    $device->application_id,
                    $device->device_eui,
                    $options['expected_message_count'] ?? 1,
                    $options['message_timeout'] ?? 60
                ),
                'tx' => $this->chirpStackService->checkDeviceTxMessages(
                    $device->chirpstackServer,
                    $device->application_id,
                    $device->device_eui,
                    $options['message_payload'] ?? 'test',
                    $options['expect_ack'] ?? true
                ),
                'telemetry' => $this->thingsBoardService->checkTelemetryData(
                    $device->thingsboardServer,
                    $device->device_eui,
                    $options['data_points'] ?? ['temperature', 'humidity']
                ),
                'rpc' => $this->thingsBoardService->executeRpcCall(
                    $device->thingsboardServer,
                    $device->device_eui,
                    $options['method'] ?? 'getValue',
                    $options['params'] ?? []
                ),
                default => throw new \InvalidArgumentException("Unsupported MQTT check type: {$testType}"),
            };

            $responseTime = (int)((microtime(true) - $startTime) * 1000);
            
            return array_merge($result, [
                'response_time_ms' => $responseTime,
                'success' => $result['success'] ?? false,
                'error_message' => $result['error_message'] ?? null,
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage(),
                'response_time_ms' => (int)((microtime(true) - $startTime) * 1000),
            ];
        }
    }

    /**
     * Check device status via HTTP
     * @param Device $device The device to check
     * @param string $testType The type of test being performed
     * @return DeviceMonitoringResult The monitoring result
     */
    public function checkHttpStatus(Device $device, string $testType): DeviceMonitoringResult
    {
        $startTime = microtime(true);
        // Check ChirpStack device status via HTTP
        $chirpStackStatus = $this->chirpStackService->getDeviceStatusHttp(
            $device->chirpstackServer,
            $device->application_id,
            $device->device_eui
        );
        $chirpStackTime = (int)((microtime(true) - $startTime) * 1000);

        $startTime = microtime(true);
        // Check ThingsBoard device status via HTTP
        $thingsBoardStatus = $this->thingsBoardService->getDeviceStatusHttp(
            $device->thingsboardServer,
            $device->device_eui
        );
        $thingsBoardTime = (int)((microtime(true) - $startTime) * 1000);

        return $this->createResult(
            device: $device,
            success: $chirpStackStatus && $thingsBoardStatus,
            errorMessage: null,
            testType: $testType,
            chirpstackStatus: $chirpStackStatus,
            thingsboardStatus: $thingsBoardStatus,
            chirpstackResponseTime: $chirpStackTime,
            thingsboardResponseTime: $thingsBoardTime,
        );
    }

    /**
     * Create and store a monitoring result
     * @param Device $device The device being monitored
     * @param bool $success Whether the monitoring was successful
     * @param string|null $errorMessage Optional error message
     * @param string $testType The type of test performed
     * @param bool|null $chirpstackStatus ChirpStack status
     * @param bool|null $thingsboardStatus ThingsBoard status
     * @param int|null $chirpstackResponseTime ChirpStack response time in ms
     * @param int|null $thingsboardResponseTime ThingsBoard response time in ms
     * @return DeviceMonitoringResult The created monitoring result
     */
    protected function createResult(
        Device $device,
        bool $success,
        ?string $errorMessage = null,
        string $testType = 'scheduled',
        ?bool $chirpstackStatus = false,
        ?bool $thingsboardStatus = false,
        ?int $chirpstackResponseTime = null,
        ?int $thingsboardResponseTime = null,
    ): DeviceMonitoringResult {
        $result = new DeviceMonitoringResult([
            'device_id' => $device->id,
            'chirpstack_status' => $chirpstackStatus,
            'thingsboard_status' => $thingsboardStatus,
            'chirpstack_response_time' => $chirpstackResponseTime,
            'thingsboard_response_time' => $thingsboardResponseTime,
            'success' => $success,
            'error_message' => $errorMessage,
            'test_type' => $testType,
        ]);

        $result->save();

        // Update device status
        $device->update([
            'is_active' => $success,
            'last_seen_at' => $success ? now() : $device->last_seen_at,
        ]);

        return $result;
    }
}
