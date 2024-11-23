<?php

namespace App\Services\ThingsBoard;

use App\Http\Integrations\ThingsBoardHttp\ThingsBoardHttp;
use App\Http\Integrations\ThingsBoardHttp\Requests\LoginHttpRequest;

class ThingsBoardService
{
    protected ?string $token = null;

    public function __construct(
        protected ThingsBoardHttp $client,
        protected string $username,
        protected string $password
    ) {}

    public function login(): void
    {
        $response = $this->client->send(new LoginHttpRequest([
            'username' => $this->username,
            'password' => $this->password,
        ]));

        if (!$response->successful()) {
            throw new \Exception('Failed to authenticate with ThingsBoard');
        }

        $this->token = $response->json('token');
        $this->client->authenticate($this->token);
    }

    public function getDevices(): array
    {
        if (!$this->token) {
            $this->login();
        }

        // TODO: Implement device retrieval
        return [];
    }

    public function createDevice(array $data): array
    {
        if (!$this->token) {
            $this->login();
        }

        // TODO: Implement device creation
        return [];
    }
}
