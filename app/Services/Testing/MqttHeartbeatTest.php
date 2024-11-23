<?php

namespace App\Services\Testing;

use App\Services\Mqtt\ThingsBoardMqttClient;
use Carbon\Carbon;

class MqttHeartbeatTest implements TestInterface
{
    protected ThingsBoardMqttClient $mqttClient;
    protected string $requestId;
    protected bool $responseReceived = false;
    protected Carbon $startTime;
    protected ?string $errorMessage = null;
    protected ?float $responseTime = null;

    public function __construct(ThingsBoardMqttClient $mqttClient)
    {
        $this->mqttClient = $mqttClient;
    }

    public function execute(): TestResult
    {
        try {
            $this->startTime = now();
            $this->requestId = uniqid('hb_', true);
            
            // Send heart-beat message
            $this->mqttClient->sendTelemetry([
                'heartbeat' => [
                    'requestId' => $this->requestId,
                    'timestamp' => now()->toIso8601String(),
                    'type' => 'mqtt-heartbeat'
                ]
            ]);

            // Wait for response with timeout
            $timeout = now()->addSeconds(30);
            while (!$this->responseReceived && now()->lt($timeout)) {
                usleep(100000); // 100ms
            }

            if (!$this->responseReceived) {
                throw new \RuntimeException('Heart-beat response timeout');
            }

            return new TestResult(
                success: true,
                responseTime: $this->responseTime
            );
        } catch (\Exception $e) {
            return new TestResult(
                success: false,
                errorMessage: $e->getMessage()
            );
        }
    }

    public function getName(): string
    {
        return 'MQTT Heartbeat Test';
    }

    /**
     * Handle heart-beat response
     */
    public function handleResponse(array $response): void
    {
        if ($response['heartbeat']['requestId'] === $this->requestId) {
            $this->responseReceived = true;
            $this->responseTime = now()->diffInMilliseconds($this->startTime);
        }
    }
}
