<?php

namespace App\Services\Mqtt;

use App\Models\MonitoringDevice;
use RuntimeException;

class ThingsBoardMqttClient extends MqttClient
{
    private MonitoringDevice $device;

    public function __construct(MonitoringDevice $device, $phpMqttClient = null)
    {
        $this->device = $device;
        $settings = $device->settings;
        $credentials = $device->credentials;

        $config = [
            'host' => $settings['host'] ?? 'localhost',
            'port' => $settings['port'] ?? 1883,
            'client_id' => "tb_monitor_{$device->id}",
            'username' => $credentials['thingsboard_access_token'] ?? throw new RuntimeException('Access token is required'),
            'last_will_topic' => "v1/devices/me/attributes",
            'last_will_message' => json_encode(['status' => 'offline']),
            'last_will_qos' => 1
        ];

        parent::__construct($config, $phpMqttClient);
    }

    public function sendTelemetry(array $data): void
    {
        $payload = json_encode($data);
        $this->publish('v1/devices/me/telemetry', $payload);
    }

    public function sendAttributes(array $attributes): void
    {
        $payload = json_encode($attributes);
        $this->publish('v1/devices/me/attributes', $payload);
    }

    public function subscribeToRpcRequests(callable $callback): void
    {
        $this->subscribe('v1/devices/me/rpc/request/+', function ($topic, $message) use ($callback) {
            // Extract request ID from topic
            preg_match('/v1\/devices\/me\/rpc\/request\/(\d+)/', $topic, $matches);
            $requestId = $matches[1] ?? null;

            if ($requestId === null) {
                throw new RuntimeException("Invalid RPC request topic format: {$topic}");
            }

            // Parse message
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in RPC request: " . json_last_error_msg());
            }

            // Call callback with parsed data
            $response = $callback($payload, $requestId);

            // Send response
            if ($response !== null) {
                $this->publish("v1/devices/me/rpc/response/{$requestId}", json_encode($response));
            }
        });
    }

    public function sendHeartbeat(): void
    {
        $heartbeat = [
            'timestamp' => time() * 1000,
            'status' => 'online',
            'device_id' => $this->device->id,
            'type' => 'heartbeat'
        ];

        $this->sendTelemetry($heartbeat);
    }

    public function reportStatus(string $status, ?string $message = null): void
    {
        $attributes = [
            'status' => $status,
            'lastStatusUpdate' => time() * 1000
        ];

        if ($message !== null) {
            $attributes['statusMessage'] = $message;
        }

        $this->sendAttributes($attributes);
    }
}
