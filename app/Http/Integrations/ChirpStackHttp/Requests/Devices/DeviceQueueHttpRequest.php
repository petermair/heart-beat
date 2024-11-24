<?php

namespace App\Http\Integrations\ChirpStackHttp\Requests\Devices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DeviceQueueHttpRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $devEui,
        protected array $queueItem
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/devices/{$this->devEui}/queue";
    }

    protected function defaultBody(): array
    {
        return [
            'deviceQueueItem' => $this->queueItem,
        ];
    }
}
