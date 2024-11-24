<?php

namespace App\Services\Mqtt;

use App\Models\Device;
use App\DataTransferObjects\ChirpStackMessageDto;
use RuntimeException;

class ChirpStackMqttClient extends MqttClient
{
    private Device $device;
    private string $applicationId;

    public function __construct(Device $device, $phpMqttClient = null)
    {
        $this->device = $device;
        $server = $device->chirpstackServer;
        $broker = $server->mqttBroker;

        if (!$broker) {
            throw new RuntimeException('MQTT broker configuration is required');
        }

        $this->applicationId = $device->application_id;
        
        $config = [
            'host' => $broker->host,
            'port' => $broker->port,
            'client_id' => "cs_monitor_{$device->id}",
            'username' => $server->credentials['api_key'] ?? throw new RuntimeException('API key is required'),
            'password' => '',
            'last_will_topic' => $this->getDeviceStatusTopic(),
            'last_will_message' => json_encode(['status' => 'offline']),
            'last_will_qos' => 1
        ];

        parent::__construct($config, $phpMqttClient);
    }

    private function getDeviceStatusTopic(): string
    {
        return "application/{$this->applicationId}/device/{$this->device->device_eui}/status";
    }

    private function getUplinkTopic(): string
    {
        return "application/{$this->applicationId}/device/+/rx";
    }

    private function getDownlinkTopic(): string
    {
        return "application/{$this->applicationId}/device/+/tx";
    }

    private function getJoinTopic(): string
    {
        return "application/{$this->applicationId}/device/+/join";
    }

    private function getAckTopic(): string
    {
        return "application/{$this->applicationId}/device/+/ack";
    }

    private function getErrorTopic(): string
    {
        return "application/{$this->applicationId}/device/+/error";
    }

    public function subscribeToUplink(callable $callback): void
    {
        $this->subscribe($this->getUplinkTopic(), function ($topic, $message) use ($callback) {
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in uplink message: " . json_last_error_msg());
            }
            $callback(ChirpStackMessageDto::fromUplink($payload), $topic);
        });
    }

    public function subscribeToDownlink(callable $callback): void
    {
        $this->subscribe($this->getDownlinkTopic(), function ($topic, $message) use ($callback) {
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in downlink message: " . json_last_error_msg());
            }
            $callback(ChirpStackMessageDto::fromDownlink($payload), $topic);
        });
    }

    public function subscribeToJoin(callable $callback): void
    {
        $this->subscribe($this->getJoinTopic(), function ($topic, $message) use ($callback) {
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in join message: " . json_last_error_msg());
            }
            $callback(ChirpStackMessageDto::fromJoin($payload), $topic);
        });
    }

    public function subscribeToAck(callable $callback): void
    {
        $this->subscribe($this->getAckTopic(), function ($topic, $message) use ($callback) {
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in ack message: " . json_last_error_msg());
            }
            $callback(ChirpStackMessageDto::fromAck($payload), $topic);
        });
    }

    public function subscribeToError(callable $callback): void
    {
        $this->subscribe($this->getErrorTopic(), function ($topic, $message) use ($callback) {
            $payload = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON in error message: " . json_last_error_msg());
            }
            $callback(ChirpStackMessageDto::fromError($payload, $payload['error'] ?? 'Unknown error'), $topic);
        });
    }

    public function sendDownlink(string $deviceEui, array $data, int $fPort = 2, bool $confirm = true): void
    {
        $payload = [
            'deviceQueueItem' => [
                'confirmed' => $confirm,
                'data' => base64_encode(json_encode($data)),
                'devEUI' => $deviceEui,
                'fPort' => $fPort
            ]
        ];

        $topic = "application/{$this->applicationId}/device/{$deviceEui}/command/down";
        $this->publish($topic, json_encode($payload));
    }

    public function reportDeviceStatus(string $status, array $metadata = []): void
    {
        $payload = array_merge([
            'status' => $status,
            'timestamp' => time() * 1000,
            'device_id' => $this->device->id
        ], $metadata);

        $this->publish($this->getDeviceStatusTopic(), json_encode($payload));
    }
}
