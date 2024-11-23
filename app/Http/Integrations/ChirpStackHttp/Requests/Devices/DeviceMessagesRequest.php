<?php

namespace App\Http\Integrations\ChirpStackHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeviceMessagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $applicationId,
        protected string $deviceEui
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/applications/{$this->applicationId}/devices/{$this->deviceEui}/messages";
    }
}
