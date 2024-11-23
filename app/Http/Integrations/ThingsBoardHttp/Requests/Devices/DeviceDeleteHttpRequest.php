<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeviceDeleteHttpRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $deviceId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/device/{$this->deviceId}";
    }
}
