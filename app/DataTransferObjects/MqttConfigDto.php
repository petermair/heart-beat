<?php

namespace App\DataTransferObjects;

class MqttConfigDto
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $clientId,
        public readonly ?string $username = null,
        public readonly ?string $password = null,
        public readonly ?string $lastWillTopic = null,
        public readonly ?string $lastWillMessage = null,
        public readonly int $lastWillQos = 1,
        public readonly int $keepAliveInterval = 60,
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            host: $config['host'] ?? 'localhost',
            port: $config['port'] ?? 1883,
            clientId: $config['client_id'] ?? uniqid('mqtt_'),
            username: $config['username'] ?? null,
            password: $config['password'] ?? null,
            lastWillTopic: $config['last_will_topic'] ?? null,
            lastWillMessage: $config['last_will_message'] ?? null,
            lastWillQos: $config['last_will_qos'] ?? 1,
            keepAliveInterval: $config['keep_alive_interval'] ?? 60,
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'client_id' => $this->clientId,
            'username' => $this->username,
            'password' => $this->password,
            'last_will_topic' => $this->lastWillTopic,
            'last_will_message' => $this->lastWillMessage,
            'last_will_qos' => $this->lastWillQos,
            'keep_alive_interval' => $this->keepAliveInterval,
        ];
    }
}
