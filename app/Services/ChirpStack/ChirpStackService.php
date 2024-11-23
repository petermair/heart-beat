<?php

namespace App\Services\ChirpStack;

use App\Http\Integrations\ChirpStackHttp\ChirpStackHttp;

class ChirpStackService
{
    public function __construct(
        protected ChirpStackHttp $client
    ) {}

    public function getDeviceInfo(string $deviceId): array
    {
        // TODO: Implement device info retrieval
        return [];
    }

    public function getDeviceStatus(string $deviceId): array
    {
        // TODO: Implement device status retrieval
        return [];
    }
}
