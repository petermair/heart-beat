<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SendDeviceTelemetryRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $deviceEui,
        protected array $telemetryData
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/plugins/telemetry/DEVICE/{$this->deviceEui}/timeseries/values";
    }

    protected function defaultBody(): array
    {
        return $this->telemetryData;
    }
}
