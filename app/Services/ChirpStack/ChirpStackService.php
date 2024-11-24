<?php

namespace App\Services\ChirpStack;

use App\Http\Integrations\ChirpStackHttp\ChirpStackHttp;
use App\Models\Device;
use Exception;

class ChirpStackService
{
    public function __construct(
        protected ChirpStackHttp $client
    ) {}

    /**
     * Get device information from ChirpStack
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @return array<string, mixed> Device information
     */
    public function getDeviceInfo(string $server, string $applicationId, string $deviceEui): array
    {
        return $this->client->setBaseUrl($server)
            ->device($applicationId, $deviceEui)
            ->json();
    }

    /**
     * Get device status from ChirpStack
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatus(string $server, string $applicationId, string $deviceEui): bool
    {
        try {
            $response = $this->client->setBaseUrl($server)
                ->deviceStatus($applicationId, $deviceEui)
                ->json();

            return isset($response['lastSeenAt']) && strtotime($response['lastSeenAt']) > strtotime('-5 minutes');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get device status from ChirpStack via HTTP
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatusHttp(string $server, string $applicationId, string $deviceEui): bool
    {
        try {
            $response = $this->client->setBaseUrl($server)
                ->deviceStatus($applicationId, $deviceEui)
                ->json();

            return isset($response['lastSeenAt']) && strtotime($response['lastSeenAt']) > strtotime('-5 minutes');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check device RX messages
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  int  $expectedCount  Expected number of messages
     * @param  int  $timeout  Timeout in seconds
     * @return array Check result
     */
    public function checkDeviceRxMessages(
        string $server,
        string $applicationId,
        string $deviceEui,
        int $expectedCount = 1,
        int $timeout = 60
    ): array {
        try {
            $response = $this->client->setBaseUrl($server)
                ->deviceMessages($applicationId, $deviceEui)
                ->json();

            $messages = $response['result'] ?? [];
            $recentMessages = array_filter($messages, function ($msg) use ($timeout) {
                return isset($msg['receivedAt']) &&
                    strtotime($msg['receivedAt']) > strtotime("-{$timeout} seconds");
            });

            $messagesReceived = count($recentMessages);

            return [
                'success' => $messagesReceived >= $expectedCount,
                'messages_received' => $messagesReceived,
                'error_message' => $messagesReceived < $expectedCount
                    ? "Expected {$expectedCount} messages but received {$messagesReceived}"
                    : null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'messages_received' => 0,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check device TX messages
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  string  $payload  Message payload
     * @param  bool  $expectAck  Whether to expect acknowledgment
     * @return array Check result
     */
    public function checkDeviceTxMessages(
        string $server,
        string $applicationId,
        string $deviceEui,
        string $payload = 'test',
        bool $expectAck = true
    ): array {
        try {
            $response = $this->client->setBaseUrl($server)
                ->sendDeviceMessage($applicationId, $deviceEui, [
                    'confirmed' => $expectAck,
                    'data' => base64_encode($payload),
                ])
                ->json();

            $ackReceived = $response['status'] ?? false;

            return [
                'success' => ! $expectAck || $ackReceived,
                'ack_received' => $ackReceived,
                'error_message' => $expectAck && ! $ackReceived
                    ? 'Message sent but no acknowledgment received'
                    : null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'ack_received' => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Wait for a device message
     *
     * @param  Device  $device  Device to wait for
     * @param  int  $timeout  Timeout in seconds
     * @return bool Success status
     */
    public function waitForDeviceMessage(Device $device, int $timeout = 30): bool
    {
        try {
            $startTime = time();
            while (time() - $startTime < $timeout) {
                $response = $this->client->getDeviceMessages($device->id);
                if ($response->ok() && ! empty($response->json())) {
                    return true;
                }
                sleep(1);
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Simulate a device uplink
     *
     * @param  Device  $device  Device to simulate
     * @param  array<string, mixed>  $data  Uplink data
     * @return bool Success status
     */
    public function simulateDeviceUplink(Device $device, array $data): bool
    {
        try {
            $response = $this->client->simulateUplink($device->id, $data);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test MQTT connection
     *
     * @param  Device  $device  Device to test
     * @return bool Success status
     */
    public function testMqttConnection(Device $device): bool
    {
        try {
            $response = $this->client->testMqttConnection($device->id);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test HTTP connection
     *
     * @param  Device  $device  Device to test
     * @return bool Success status
     */
    public function testHttpConnection(Device $device): bool
    {
        try {
            $response = $this->client->testHttpConnection($device->id);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }
}
