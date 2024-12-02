<?php

namespace App\Services\ChirpStack;

use App\Http\Integrations\ChirpStackHttp\ChirpStackHttp;
use App\Models\Device;
use Exception;
use Illuminate\Support\Carbon;

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
                ->deviceMessage($applicationId, $deviceEui, [
                    'payload' => $payload,
                    'expectAck' => $expectAck,
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
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  int  $timeout  Timeout in seconds
     * @return bool Success status
     */
    public function waitForDeviceMessage(string $server, string $applicationId, string $deviceEui, int $timeout = 30): bool
    {
        try {
            $startTime = time();
            while (time() - $startTime < $timeout) {
                $response = $this->client->getDeviceMessages($applicationId, $deviceEui);
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
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  array<string, mixed>  $data  Uplink data
     * @return bool Success status
     */
    public function simulateDeviceUplink(string $server, string $applicationId, string $deviceEui, array $data): bool
    {
        try {
            $response = $this->client->simulateUplink($applicationId, $deviceEui, $data);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test MQTT connection
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @return bool Success status
     */
    public function testMqttConnection(string $server, string $applicationId, string $deviceEui): bool
    {
        try {
            $response = $this->client->testMqttConnection($applicationId, $deviceEui);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test HTTP connection
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @return bool Success status
     */
    public function testHttpConnection(string $server, string $applicationId, string $deviceEui): bool
    {
        try {
            $response = $this->client->testHttpConnection($applicationId, $deviceEui);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Wait for a device response
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  int  $messageId  The message ID to wait for
     * @param  int  $timeout  Timeout in seconds
     * @return array The wait result
     */
    public function waitForDeviceResponse(
        string $server,
        string $applicationId,
        string $deviceEui,
        int $messageId,
        int $timeout = 30
    ): array {
        try {
            $startTime = microtime(true);
            $endTime = $startTime + $timeout;

            while (microtime(true) < $endTime) {
                $response = $this->client->setBaseUrl($server)
                    ->deviceResponse($applicationId, $deviceEui, $messageId)
                    ->json();

                if (isset($response['messageId']) && $response['messageId'] === $messageId) {
                    return [
                        'success' => true,
                        'response' => $response,
                        'duration' => (microtime(true) - $startTime) * 1000,
                    ];
                }

                // Wait a bit before next check
                usleep(500000); // 500ms
            }

            return [
                'success' => false,
                'error' => 'Timeout waiting for device response',
                'duration' => $timeout * 1000,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => (microtime(true) - $startTime) * 1000,
            ];
        }
    }

    /**
     * Send a message to a device
     *
     * @param  string  $server  The ChirpStack server URL
     * @param  string  $applicationId  The application ID
     * @param  string  $deviceEui  The device EUI
     * @param  array  $data  Message data
     * @return array Response from the server
     */
    public function sendDeviceMessage(
        string $server,
        string $applicationId,
        string $deviceEui,
        array $data
    ): array {
        try {
            $response = $this->client->setBaseUrl($server)
                ->deviceMessage($applicationId, $deviceEui, $data)
                ->json();

            return [
                'success' => true,
                'response' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Wait for a message at the ChirpStack endpoint
     *
     * @param  string  $server  ChirpStack server URL
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @param  int  $timeout  Timeout in seconds
     * @return bool Success status
     */
    public function waitForEndpointMessage(string $server, string $applicationId, string $deviceEui, int $timeout = 30): bool
    {
        try {
            // Get the latest message from the endpoint
            $response = $this->client->setBaseUrl($server)
                ->deviceMessages($applicationId, $deviceEui)
                ->json();

            // Check if we have any messages in the last $timeout seconds
            $now = now();
            foreach ($response['messages'] ?? [] as $message) {
                $messageTime = Carbon::parse($message['timestamp']);
                if ($messageTime->diffInSeconds($now) <= $timeout) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
