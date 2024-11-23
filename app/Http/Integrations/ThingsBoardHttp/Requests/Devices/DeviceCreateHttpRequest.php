<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DeviceCreateHttpRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected array $deviceData
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/device';
    }

    protected function defaultBody(): array
    {
        return $this->deviceData;
    }
}