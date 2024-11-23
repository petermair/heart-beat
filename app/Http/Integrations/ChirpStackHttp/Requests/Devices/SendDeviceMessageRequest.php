<?php

namespace App\Http\Integrations\ChirpStackHttp\Requests\Devices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SendDeviceMessageRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $applicationId,
        protected string $deviceEui,
        protected array $data
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/applications/{$this->applicationId}/devices/{$this->deviceEui}/messages";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
