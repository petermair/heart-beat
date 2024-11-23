<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListDevicesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/api/tenant/devices";
    }
}
