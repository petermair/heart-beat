<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class HealthHttpRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/api/health';
    }
}
