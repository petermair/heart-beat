<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeviceTelemetryRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $deviceEui
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/plugins/telemetry/DEVICE/{$this->deviceEui}/values/timeseries";
    }
}
