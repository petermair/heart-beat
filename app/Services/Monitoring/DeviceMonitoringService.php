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
     */
    protected function checkDeviceStatus(Device $device, string $testType): DeviceMonitoringResult
    {
        $communicationType = $device->communicationType->name;

        return match ($communicationType) {
            'mqtt' => $this->checkMqttStatus($device, $testType),
            'http' => $this->checkHttpStatus($device, $testType),
            default => throw new \InvalidArgumentException("Unsupported communication type: {$communicationType}"),
        };
    }

    /**
     * Check device status via MQTT
     */
    protected function checkMqttStatus(Device $device, string $testType): DeviceMonitoringResult
    {
        $startTime = microtime(true);
        
        // Check ChirpStack device status
        $chirpStackStatus = $this->chirpStackService->getDeviceStatus(
            $device->chirpstackServer,
            $device->application_id,
            $device->device_eui
        );
        $chirpStackTime = (microtime(true) - $startTime) * 1000;

        $startTime = microtime(true);
        // Check ThingsBoard device status
        $thingsBoardStatus = $this->thingsBoardService->getDeviceStatus(
            $device->thingsboardServer,
            $device->device_eui
        );
        $thingsBoardTime = (microtime(true) - $startTime) * 1000;

        return $this->createResult(
            device: $device,
            success: $chirpStackStatus && $thingsBoardStatus,
            errorMessage: null,
            testType: $testType,
            chirpstackStatus: $chirpStackStatus,
            thingsboardStatus: $thingsBoardStatus,
            chirpstackResponseTime: (int) $chirpStackTime,
            thingsboardResponseTime: (int) $thingsBoardTime,
        );
    }

    /**
     * Check device status via HTTP
     */
    protected function checkHttpStatus(Device $device, string $testType): DeviceMonitoringResult
    {
        $startTime = microtime(true);
        // Check ChirpStack device status via HTTP
        $chirpStackStatus = $this->chirpStackService->getDeviceStatusHttp(
            $device->chirpstackServer,
            $device->application_id,
            $device->device_eui
        );
        $chirpStackTime = (microtime(true) - $startTime) * 1000;

        $startTime = microtime(true);
        // Check ThingsBoard device status via HTTP
        $thingsBoardStatus = $this->thingsBoardService->getDeviceStatusHttp(
            $device->thingsboardServer,
            $device->device_eui
        );
        $thingsBoardTime = (microtime(true) - $startTime) * 1000;

        return $this->createResult(
            device: $device,
            success: $chirpStackStatus && $thingsBoardStatus,
            errorMessage: null,
            testType: $testType,
            chirpstackStatus: $chirpStackStatus,
            thingsboardStatus: $thingsBoardStatus,
            chirpstackResponseTime: (int) $chirpStackTime,
            thingsboardResponseTime: (int) $thingsBoardTime,
        );
    }

    /**
     * Create and store a monitoring result
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
