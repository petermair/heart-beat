<?php

namespace App\Services\Testing;

class TestResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $errorMessage = null,
        public readonly ?float $responseTime = null
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error_message' => $this->errorMessage,
            'response_time' => $this->responseTime,
        ];
    }
}
