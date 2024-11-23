<?php

namespace App\Services\Mqtt;

use App\Models\MonitoringDevice;
use App\DataTransferObjects\MessagePayloadDto;
use App\DataTransferObjects\MessageRouteDto;
use App\DataTransferObjects\ChirpStackMessageDto;
use App\DataTransferObjects\ThingsBoardMessageDto;
use RuntimeException;

class MqttMonitor
{
    private MonitoringDevice $device;
    private ThingsBoardMqttClient $thingsboardClient;
    private ChirpStackMqttClient $chirpstackClient;

    public function __construct(
        MonitoringDevice $device,
        ?ThingsBoardMqttClient $thingsboardClient = null,
        ?ChirpStackMqttClient $chirpstackClient = null
    ) {
        $this->device = $device;
        $this->thingsboardClient = $thingsboardClient ?? new ThingsBoardMqttClient($device);
        $this->chirpstackClient = $chirpstackClient ?? new ChirpStackMqttClient($device);
    }

    public function setClients(ThingsBoardMqttClient $thingsboardClient, ChirpStackMqttClient $chirpstackClient): void
    {
        $this->thingsboardClient = $thingsboardClient;
        $this->chirpstackClient = $chirpstackClient;
    }

    public function startMonitoring(): void
    {
        // Connect to both platforms
        $this->thingsboardClient->connect();
        $this->chirpstackClient->connect();

        // Set up monitoring based on device type
        switch ($this->device->device_type) {
            case 'RX':
                $this->monitorRxPath();
                break;
            case 'TX':
                $this->monitorTxPath();
                break;
            case 'HEALTH':
                $this->monitorHealth();
                break;
            default:
                throw new RuntimeException("Unknown device type: {$this->device->device_type}");
        }
    }

    public function stopMonitoring(): bool
    {
        $this->thingsboardClient->disconnect();
        $this->chirpstackClient->disconnect();
        return true;
    }

    private function monitorRxPath(): void
    {
        // Monitor uplink messages from ChirpStack
        $this->chirpstackClient->subscribeToUplink(function (ChirpStackMessageDto $message, string $topic) {
            // Create message payload record
            $messagePayload = MessagePayloadDto::create([
                'id' => time(), // Using timestamp as a simple ID generator
                'data' => $message->data,
                'deviceEui' => $message->deviceEui,
                'rssi' => $message->rssi ?? 0.0,
                'snr' => $message->snr ?? 0.0
            ]);

            // Record ChirpStack reception
            MessageRouteDto::create([
                'id' => time(),
                'message_payload_id' => $messagePayload->id,
                'source' => $this->device['chirpstack_server']->name ?? 'chirpstack',
                'destination' => 'thingsboard'
            ]);

            // Forward to ThingsBoard
            try {
                $this->thingsboardClient->sendTelemetry([
                    'data' => $message->data,
                    'metadata' => [
                        'deviceEUI' => $message->deviceEui,
                        'rssi' => $message->rssi,
                        'snr' => $message->snr
                    ]
                ]);

                // Record successful forwarding
                MessageRouteDto::create([
                    'id' => time() + 1,
                    'message_payload_id' => $messagePayload->id,
                    'source' => 'thingsboard',
                    'destination' => 'client',
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                // Log error and continue
                error_log("Failed to forward message to ThingsBoard: " . $e->getMessage());
            }
        });
    }

    private function monitorTxPath(): void
    {
        // Monitor RPC requests from ThingsBoard
        $this->thingsboardClient->subscribeToRpc(function (ThingsBoardMessageDto $request, string $requestId) {
            // Create message payload record
            $messagePayload = MessagePayloadDto::create([
                'id' => 1,
                'data' => $request->params ?? [],
                'deviceEui' => $this->device->credentials['thingsboard_device_eui'] ?? '',
                'rssi' => 0.0,
                'snr' => 0.0
            ]);

            // Record ThingsBoard reception
            MessageRouteDto::create([
                'id' => 1,
                'message_payload_id' => $messagePayload->id,
                'source' => $this->device->thingsboard_server->name ?? 'thingsboard',
                'destination' => 'chirpstack'
            ]);

            try {
                // Forward to ChirpStack
                $this->chirpstackClient->sendDownlink(
                    $this->device->credentials['chirpstack_device_eui'],
                    $request->params ?? [],
                    2, // Use fPort 2 for commands
                    true // Require confirmation
                );

                // Record successful forwarding
                MessageRouteDto::create([
                    'id' => 2,
                    'message_payload_id' => $messagePayload->id,
                    'source' => 'chirpstack',
                    'destination' => 'device'
                ]);

                // Acknowledge successful processing
                $this->thingsboardClient->sendRpcResponse($requestId, ['success' => true]);
            } catch (\Exception $e) {
                // Log error and continue
                error_log("Failed to forward message to ChirpStack: " . $e->getMessage());

                // Acknowledge failure
                $this->thingsboardClient->sendRpcResponse($requestId, [
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    private function monitorHealth(): void
    {
        // Monitor device health by subscribing to join events and status updates
        $this->chirpstackClient->subscribeToJoin(function (array $event) {
            $this->thingsboardClient->reportDeviceStatus('online', [
                'lastJoinTime' => time(),
                'devAddr' => $event['devAddr'] ?? null
            ]);
        });
    }
}