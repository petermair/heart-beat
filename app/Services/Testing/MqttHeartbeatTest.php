<?php

namespace App\Services\Testing;

use App\Services\Mqtt\ThingsBoardMqttClient;

class MqttHeartbeatTest extends TestCase
{
    protected ThingsBoardMqttClient $mqttClient;
    protected string $requestId;
    protected bool $responseReceived = false;

    public function execute(): void
    {
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
        $timeout = now()->addSeconds($this->getTimeout());
        while (!$this->responseReceived && now()->lt($timeout)) {
            usleep(100000); // 100ms
        }

        if (!$this->responseReceived) {
            throw new \RuntimeException('Heart-beat response timeout');
        }

        $this->setStatus('completed');
    }

    /**
     * Handle heart-beat response
     */
    public function handleResponse(array $response): void
    {
        if ($response['heartbeat']['requestId'] === $this->requestId) {
            $this->responseReceived = true;
            $this->addResult('responseTime', now()->diffInMilliseconds($this->startTime));
        }
    }
}
