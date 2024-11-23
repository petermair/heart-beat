<?php

namespace App\Services\ThingsBoard;

use App\Http\Integrations\ThingsBoardHttp\ThingsBoardHttp;
use App\Http\Integrations\ThingsBoardHttp\Requests\LoginHttpRequest;

/**
 * ThingsBoard service class
 */
class ThingsBoardService
{
    /**
     * Authentication token
     *
     * @var string|null
     */
    protected ?string $token = null;

    /**
     * ThingsBoard HTTP client
     *
     * @var ThingsBoardHttp
     */
    protected ThingsBoardHttp $client;

    /**
     * ThingsBoard username
     *
     * @var string
     */
    protected string $username;

    /**
     * ThingsBoard password
     *
     * @var string
     */
    protected string $password;

    /**
     * Constructor
     *
     * @param ThingsBoardHttp $client ThingsBoard HTTP client
     * @param string $username ThingsBoard username
     * @param string $password ThingsBoard password
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

        if (!$response->successful()) {
            throw new \Exception('Failed to authenticate with ThingsBoard');
        }

        $this->token = $response->json('token');
        $this->client->authenticate($this->token);
    }

    /**
     * Get list of devices from ThingsBoard
     *
     * @param string $server The ThingsBoard server URL
     * @return array<string, mixed> List of devices
     * @throws \Exception When authentication fails
     */
    public function getDevices(string $server): array
    {
        if (!$this->token) {
            $this->login();
        }

        return $this->client->setBaseUrl($server)
            ->devices()
            ->json();
    }

    /**
     * Get device status from ThingsBoard
     *
     * @param string $server The ThingsBoard server URL
     * @param string $deviceEui The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatus(string $server, string $deviceEui): bool
    {
        try {
            if (!$this->token) {
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
     * @param string $server The ThingsBoard server URL
     * @param string $deviceEui The device EUI
     * @return bool True if device is active, false otherwise
     */
    public function getDeviceStatusHttp(string $server, string $deviceEui): bool
    {
        try {
            if (!$this->token) {
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
     * @param string $server The ThingsBoard server URL
     * @param array<string, mixed> $data Device data
     * @return array<string, mixed> Created device data
     * @throws \Exception When authentication fails
     */
    public function createDevice(string $server, array $data): array
    {
        if (!$this->token) {
            $this->login();
        }

        return $this->client->setBaseUrl($server)
            ->createDevice($data)
            ->json();
    }

    /**
     * Check device telemetry data
     * @param string $server The ThingsBoard server URL
     * @param string $deviceEui The device EUI
     * @param array $dataPoints Expected data points
     * @return array Check result
     */
    public function checkTelemetryData(string $server, string $deviceEui, array $dataPoints = []): array
    {
        try {
            if (!$this->token) {
                $this->login();
            }

            $response = $this->client->setBaseUrl($server)
                ->deviceTelemetry($deviceEui)
                ->json();

            $foundDataPoints = [];
            foreach ($dataPoints as $point) {
                if (isset($response[$point]) && !empty($response[$point])) {
                    $foundDataPoints[] = $point;
                }
            }

            $allFound = count($foundDataPoints) === count($dataPoints);
            
            return [
                'success' => $allFound,
                'data_points_found' => $foundDataPoints,
                'error_message' => !$allFound 
                    ? 'Missing required data points: ' . implode(', ', array_diff($dataPoints, $foundDataPoints))
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
     * @param string $server The ThingsBoard server URL
     * @param string $deviceEui The device EUI
     * @param string $method RPC method name
     * @param array $params RPC method parameters
     * @return array Call result
     */
    public function executeRpcCall(string $server, string $deviceEui, string $method, array $params = []): array
    {
        try {
            if (!$this->token) {
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
                'error_message' => !isset($response['response'])
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
}