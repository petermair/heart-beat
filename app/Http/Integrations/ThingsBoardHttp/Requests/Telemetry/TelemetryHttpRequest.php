<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Telemetry;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class TelemetryHttpRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $accessToken,
        protected array $telemetry
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/{$this->accessToken}/telemetry";
    }

    protected function defaultBody(): array
    {
        return $this->telemetry;
    }
}
