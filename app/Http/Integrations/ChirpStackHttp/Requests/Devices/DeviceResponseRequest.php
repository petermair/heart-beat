<?php

namespace App\Http\Integrations\ChirpStackHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeviceResponseRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $applicationId,
        protected string $deviceEui,
        protected int $messageId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/applications/{$this->applicationId}/devices/{$this->deviceEui}/response/{$this->messageId}";
    }
}
