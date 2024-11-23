<?php

namespace App\Http\Integrations\ThingsBoardHttp;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Auth\RequiresTokenAuth;

class ThingsBoardHttp extends Connector
{
    use AcceptsJson;
    use RequiresTokenAuth;

    public function __construct(
        protected string $baseUrl,
        protected ?string $token = null
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
            'connect_timeout' => 10,
        ];
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
