<?php

namespace App\DataTransferObjects;

class MessagePayloadDto
{
    public function __construct(
        public readonly int $id,
        public readonly array $data,
        public readonly string $deviceEui,
        public readonly float $rssi = 0.0,
        public readonly float $snr = 0.0
    ) {}

    public static function create(array $data): self
    {
        return new self(
            id: $data['id'],
            data: $data['data'],
            deviceEui: $data['deviceEui'],
            rssi: $data['rssi'] ?? 0.0,
            snr: $data['snr'] ?? 0.0
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'deviceEui' => $this->deviceEui,
            'rssi' => $this->rssi,
            'snr' => $this->snr
        ];
    }
}
