<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Health;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class HealthCheckHttpRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/api/health';
    }
}
