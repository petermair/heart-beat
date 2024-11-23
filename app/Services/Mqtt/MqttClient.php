<?php

namespace App\Services\Mqtt;

use PhpMqtt\Client\MqttClient as PhpMqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;

class MqttClient
{
    private ?PhpMqttClient $client;
    private ConnectionSettings $settings;
    private string $clientId;
    private array $config;

    public function __construct(array $config, ?PhpMqttClient $client = null)
    {
        $this->config = $config;
        $this->clientId = $config['client_id'] ?? uniqid('heartbeat_');
        $this->settings = $this->createConnectionSettings();
        $this->client = $client;
    }

    private function createConnectionSettings(): ConnectionSettings
    {
        return (new ConnectionSettings)
            ->setUsername($this->config['username'] ?? null)
            ->setPassword($this->config['password'] ?? null)
            ->setKeepAliveInterval(60)
            ->setLastWillTopic($this->config['last_will_topic'] ?? null)
            ->setLastWillMessage($this->config['last_will_message'] ?? null)
            ->setLastWillQualityOfService($this->config['last_will_qos'] ?? 1);
    }

    public function connect(): void
    {
        if ($this->client === null) {
            $this->client = new PhpMqttClient(
                $this->config['host'],
                $this->config['port'],
                $this->clientId
            );
        }

        if (!$this->client->isConnected()) {
            try {
                $this->client->connect($this->settings, true);
            } catch (MqttClientException $e) {
                throw new \RuntimeException("Failed to connect to MQTT broker: {$e->getMessage()}", 0, $e);
            }
        }
    }

    public function disconnect(): void
    {
        if ($this->client && $this->client->isConnected()) {
            $this->client->disconnect();
        }
    }

    public function publish(string $topic, string $message, int $qos = 1, bool $retain = false): void
    {
        try {
            $this->ensureConnection();
            $this->client->publish($topic, $message, $qos, $retain);
        } catch (MqttClientException $e) {
            throw new \RuntimeException("Failed to publish message: {$e->getMessage()}", 0, $e);
        }
    }

    public function subscribe(string $topic, callable $callback, int $qos = 1): void
    {
        try {
            $this->ensureConnection();
            $this->client->subscribe($topic, function ($topic, $message) use ($callback) {
                $callback($topic, $message);
            }, $qos);
        } catch (MqttClientException $e) {
            throw new \RuntimeException("Failed to subscribe to topic: {$e->getMessage()}", 0, $e);
        }
    }

    private function ensureConnection(): void
    {
        if (!$this->client || !$this->client->isConnected()) {
            $this->connect();
        }
    }

    public function loop(bool $allowSleep = true): void
    {
        try {
            $this->ensureConnection();
            $this->client->loop($allowSleep);
        } catch (MqttClientException $e) {
            throw new \RuntimeException("MQTT loop error: {$e->getMessage()}", 0, $e);
        }
    }
}
