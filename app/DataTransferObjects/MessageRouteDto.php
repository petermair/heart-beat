<?php

namespace App\DataTransferObjects;

class MessageRouteDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $message_payload_id,
        public readonly string $source,
        public readonly string $destination,
        public readonly string $status = 'success'
    ) {}

    public static function create(array $data): self
    {
        return new self(
            id: $data['id'],
            message_payload_id: $data['message_payload_id'],
            source: $data['source'],
            destination: $data['destination'],
            status: $data['status'] ?? 'success'
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message_payload_id' => $this->message_payload_id,
            'source' => $this->source,
            'destination' => $this->destination,
            'status' => $this->status
        ];
    }
}
