<?php

namespace App\Services\Mqtt;

class ThingsBoardMessageDto
{
    public function __construct(
        public readonly string $method,
        public readonly array $params,
        public readonly ?int $requestId = null
    ) {}

    public static function fromRpc(array $data): self
    {
        return new self(
            method: $data['method'] ?? '',
            params: $data['params'] ?? [],
            requestId: $data['requestId'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'params' => $this->params,
            'requestId' => $this->requestId,
        ];
    }
}
