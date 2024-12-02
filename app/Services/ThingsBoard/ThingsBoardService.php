<?php

namespace App\Services\ThingsBoard;

use App\Http\Integrations\ThingsBoardHttp\Requests\LoginHttpRequest;
use App\Http\Integrations\ThingsBoardHttp\ThingsBoardHttp;
use App\Models\Device;
use Exception;
use Carbon\Carbon;

/**
 * ThingsBoard service class
 */
class ThingsBoardService
{
    /**
     * Authentication token
     */
    protected ?string $token = null;

    /**
     * ThingsBoard HTTP client
     */
    protected ThingsBoardHttp $client;

    /**
     * ThingsBoard username
     */
    protected string $username;

    /**
     * ThingsBoard password
     */
    protected string $password;

    /**
     * Constructor
     *
     * @param  ThingsBoardHttp  $client  ThingsBoard HTTP client
     * @param  string  $username  ThingsBoard username
     * @param  string  $password  ThingsBoard password
     */
    public function __construct(
        ThingsBoardHttp $client,
        string $username,
        string $password
    ) {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authenticate with ThingsBoard
     *
     * @throws \Exception When authentication fails
     */
    public function login(): void
    {
        $response = $this->client->send(new LoginHttpRequest([
            'username' => $this->username,
            'password' => $this->password,
        ]));

        if (! $response->successful()) {
            throw new \Exception('Failed to authenticate with ThingsBoard');
        }

        $this->token = $response->json('token');
        $this->client->authenticate($this->token);
    }

    /**
     * Get list of devices from ThingsBoard
     *
     * @param  string  $server  The ThingsBoard server URL
     * @return array<string, mixed> List of devices
     *
     * @throws \Exception When authentication fails
     */
    public function getDevices(string $server): array
    {
        if (! $this->token) {
            $this->login();
        }

        return $this->client->setBaseUrl($server)
            ->devices()
            ->json();
    }

    /**
     * Get device status from ThingsBoard
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatus(string $server, string $deviceEui): bool
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceStatus($deviceEui)
                ->json();

            return isset($response['active']) && $response['active'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get device status from ThingsBoard via HTTP
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatusHttp(string $server, string $deviceEui): bool
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceStatus($deviceEui)
                ->json();

            return isset($response['active']) && $response['active'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a new device in ThingsBoard
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  array<string, mixed>  $data  Device data
     * @return array<string, mixed> Created device data
     *
     * @throws \Exception When authentication fails
     */
    public function createDevice(string $server, array $data): array
    {
        if (! $this->token) {
            $this->login();
        }

        return $this->client->setBaseUrl($server)
            ->createDevice($data)
            ->json();
    }

    /**
     * Check device telemetry data
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @param  array  $dataPoints  Expected data points
     * @return array Check result
     */
    public function checkTelemetryData(string $server, string $deviceEui, array $dataPoints = []): array
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceTelemetry($deviceEui)
                ->json();

            $foundDataPoints = [];
            foreach ($dataPoints as $point) {
                if (isset($response[$point]) && ! empty($response[$point])) {
                    $foundDataPoints[] = $point;
                }
            }

            $allFound = count($foundDataPoints) === count($dataPoints);

            return [
                'success' => $allFound,
                'data_points_found' => $foundDataPoints,
                'error_message' => ! $allFound
                    ? 'Missing required data points: '.implode(', ', array_diff($dataPoints, $foundDataPoints))
                    : null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data_points_found' => [],
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute RPC call on device
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @param  string  $method  RPC method name
     * @param  array  $params  RPC method parameters
     * @return array Call result
     */
    public function executeRpcCall(string $server, string $deviceEui, string $method, array $params = []): array
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceRpc($deviceEui, [
                    'method' => $method,
                    'params' => $params,
                ])
                ->json();

            return [
                'success' => isset($response['response']),
                'response' => $response['response'] ?? null,
                'error_message' => ! isset($response['response'])
                    ? 'No response received from device'
                    : null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => null,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a command to a device
     *
     * @param  Device  $device  Device to send command to
     * @param  array<string, mixed>  $command  Command data
     * @return bool Success status
     */
    public function sendDeviceCommand(Device $device, array $command): bool
    {
        try {
            $this->ensureAuthenticated();
            $response = $this->client->sendDeviceCommand($device->id, $command);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Wait for telemetry from a device
     *
     * @param  Device  $device  Device to wait for
     * @param  int  $timeout  Timeout in seconds
     * @return bool Success status
     */
    public function waitForTelemetry(Device $device, int $timeout = 30): bool
    {
        try {
            $this->ensureAuthenticated();
            $startTime = time();
            while (time() - $startTime < $timeout) {
                $telemetry = $this->client->getLatestTelemetry($device->id);
                if ($telemetry->json() && ! empty($telemetry->json())) {
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
     * Test MQTT connection
     *
     * @param  Device  $device  Device to test
     * @return bool Success status
     */
    public function testMqttConnection(Device $device): bool
    {
        try {
            $this->ensureAuthenticated();
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
            $this->ensureAuthenticated();
            $response = $this->client->testHttpConnection($device->id);

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Ensure the client is authenticated
     *
     * @throws Exception If authentication fails
     */
    protected function ensureAuthenticated(): void
    {
        if ($this->token === null) {
            $response = $this->client->send(new LoginHttpRequest([
                'username' => $this->username,
                'password' => $this->password,
            ]));

            if (! $response->ok()) {
                throw new Exception('Failed to authenticate with ThingsBoard');
            }

            $this->token = $response->json('token');
            $this->client->authenticate($this->token);
        }
    }

    /**
     * Test direct command functionality
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @param  array  $params  Test parameters
     * @return array Test result
     */
    public function testDirectCommand(string $server, string $deviceEui, array $params = []): array
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceRpc($deviceEui, [
                    'method' => 'test_direct_command',
                    'params' => $params,
                ])
                ->json();

            return [
                'success' => isset($response['response']),
                'response' => $response['response'] ?? null,
                'error_message' => ! isset($response['response'])
                    ? 'No response received from device'
                    : null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => null,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test direct telemetry functionality
     *
     * @param  string  $server  The ThingsBoard server URL
     * @param  string  $deviceEui  The device EUI
     * @param  array  $params  Test parameters
     * @return array Test result
     */
    public function testDirectTelemetry(string $server, string $deviceEui, array $params = []): array
    {
        try {
            if (! $this->token) {
                $this->login();
            }

            $startTime = time();
            $response = $this->client->setBaseUrl($server)
                ->deviceTelemetry($deviceEui, [
                    'timeWindow' => 60,
                    'keys' => implode(',', array_keys($params)),
                ])
                ->json();

            // Check if we received telemetry with matching parameters
            $success = false;
            $errorMessage = 'No matching telemetry data found';

            if (! empty($response)) {
                foreach ($response as $key => $values) {
                    if (! empty($values) && isset($params[$key])) {
                        $latestValue = end($values);
                        if ($latestValue['value'] == $params[$key] && $latestValue['ts'] >= $startTime * 1000) {
                            $success = true;
                            $errorMessage = null;
                            break;
                        }
                    }
                }
            }

            return [
                'success' => $success,
                'response' => $response,
                'error_message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => null,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Wait for a message at the ThingsBoard endpoint
     *
     * @param  string  $server  ThingsBoard server URL
     * @param  string  $deviceEui  Device EUI
     * @param  int  $timeout  Timeout in seconds
     * @return bool Success status
     */
    public function waitForEndpointMessage(string $server, string $deviceEui, int $timeout = 30): bool
    {
        try {
            // Get the latest telemetry from the endpoint
            $response = $this->client->setBaseUrl($server)
                ->deviceTelemetry($deviceEui)
                ->json();

            // Check if we have any telemetry in the last $timeout seconds
            $now = now();
            foreach ($response['data'] ?? [] as $key => $values) {
                foreach ($values as $value) {
                    $telemetryTime = Carbon::createFromTimestampMs($value['ts']);
                    if ($telemetryTime->diffInSeconds($now) <= $timeout) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}