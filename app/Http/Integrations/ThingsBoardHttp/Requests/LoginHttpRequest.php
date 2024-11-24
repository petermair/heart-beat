<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class LoginHttpRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(
        protected array $credentials
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/api/auth/login';
    }

    /**
     * The default body for the request
     */
    protected function defaultBody(): array
    {
        return $this->credentials;
    }
}
