<?php

namespace App\DataTransferObjects;

class ChirpStackMessageDto
{
    public function __construct(
        public readonly string $deviceEui,
        public readonly array $data,
        public readonly string $type, // 'uplink', 'downlink', 'join', 'ack', 'error'
        public readonly ?float $rssi = null,
        public readonly ?float $snr = null,
        public readonly ?int $fPort = null,
        public readonly bool $confirmed = false,
        public readonly ?string $error = null,
        public readonly ?array $metadata = [],
    ) {}

    public static function fromUplink(array $payload): self
    {
        return new self(
            deviceEui: $payload['deviceInfo']['devEui'] ?? '',
            data: $payload['object'] ?? $payload,
            type: 'uplink',
            rssi: $payload['rxInfo'][0]['rssi'] ?? null,
            snr: $payload['rxInfo'][0]['snr'] ?? null,
            fPort: $payload['fPort'] ?? null,
            metadata: $payload
        );
    }

    public static function fromDownlink(array $payload): self
    {
        return new self(
            deviceEui: $payload['deviceEui'] ?? '',
            data: $payload['object'] ?? [],
            type: 'downlink',
            fPort: $payload['fPort'] ?? 1,
            confirmed: $payload['confirmed'] ?? false,
            metadata: $payload
        );
    }

    public static function fromJoin(array $payload): self
    {
        return new self(
            deviceEui: $payload['deviceInfo']['devEui'] ?? '',
            data: $payload,
            type: 'join',
            metadata: $payload
        );
    }

    public static function fromAck(array $payload): self
    {
        return new self(
            deviceEui: $payload['deviceInfo']['devEui'] ?? '',
            data: $payload,
            type: 'ack',
            metadata: $payload
        );
    }

    public static function fromError(array $payload, string $error): self
    {
        return new self(
            deviceEui: $payload['deviceInfo']['devEui'] ?? '',
            data: $payload,
            type: 'error',
            error: $error,
            metadata: $payload
        );
    }

    public function toArray(): array
    {
        return [
            'deviceEui' => $this->deviceEui,
            'data' => $this->data,
            'type' => $this->type,
            'rssi' => $this->rssi,
            'snr' => $this->snr,
            'fPort' => $this->fPort,
            'confirmed' => $this->confirmed,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ];
    }
}
