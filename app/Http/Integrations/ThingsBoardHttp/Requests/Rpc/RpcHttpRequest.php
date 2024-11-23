<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Rpc;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RpcHttpRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $accessToken,
        protected string $requestId,
        protected array $payload
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/{$this->accessToken}/rpc/{$this->requestId}";
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
