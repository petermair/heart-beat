<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Rpc;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DeviceRpcRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $deviceEui,
        protected array $data
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/plugins/rpc/oneway/{$this->deviceEui}";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
