<?php

namespace App\DataTransferObjects;

class ThingsBoardMessageDto
{
    public function __construct(
        public readonly string $deviceName,
        public readonly array $telemetry,
        public readonly string $type, // 'telemetry', 'attributes', 'rpc', 'error'
        public readonly ?string $method = null,
        public readonly ?array $params = null,
        public readonly ?string $error = null,
        public readonly ?array $metadata = [],
    ) {}

    public static function fromTelemetry(array $payload): self
    {
        return new self(
            deviceName: $payload['deviceName'] ?? '',
            telemetry: $payload['values'] ?? [],
            type: 'telemetry',
            metadata: $payload
        );
    }

    public static function fromAttributes(array $payload): self
    {
        return new self(
            deviceName: $payload['deviceName'] ?? '',
            telemetry: $payload['attributes'] ?? [],
            type: 'attributes',
            metadata: $payload
        );
    }

    public static function fromRpc(array $payload): self
    {
        return new self(
            deviceName: $payload['deviceName'] ?? '',
            telemetry: [],
            type: 'rpc',
            method: $payload['method'] ?? '',
            params: $payload['params'] ?? [],
            metadata: $payload
        );
    }

    public static function fromError(array $payload, string $error): self
    {
        return new self(
            deviceName: $payload['deviceName'] ?? '',
            telemetry: [],
            type: 'error',
            error: $error,
            metadata: $payload
        );
    }

    public function toArray(): array
    {
        return [
            'deviceName' => $this->deviceName,
            'telemetry' => $this->telemetry,
            'type' => $this->type,
            'method' => $this->method,
            'params' => $this->params,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ];
    }
}
