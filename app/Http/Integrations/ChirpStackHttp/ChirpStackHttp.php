<?php

namespace App\Http\Integrations\ChirpStackHttp;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Contracts\Authenticator;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceMessagesRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceStatusRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceRequest;

class ChirpStackHttp extends Connector
{
    use AcceptsJson;

    public function __construct(
        protected string $baseUrl,
        protected ?string $token = null
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    protected function defaultAuth(): ?TokenAuthenticator
    {
        return $this->token ? new TokenAuthenticator($this->token) : null;
    }

    public function authenticate(Authenticator $authenticator): static
    {
        parent::authenticate($authenticator);
        if ($authenticator instanceof TokenAuthenticator) {
            $this->token = $authenticator->token;
        }
        return $this;
    }

    public function withToken(string $token): static
    {
        $this->token = $token;
        return $this->authenticate(new TokenAuthenticator($token));
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

    /**
     * Get device messages
     * @param string $applicationId Application ID
     * @param string $deviceEui Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceMessages(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceMessagesRequest($applicationId, $deviceEui));
    }

    /**
     * Get device status
     * @param string $applicationId Application ID
     * @param string $deviceEui Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceStatus(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceStatusRequest($applicationId, $deviceEui));
    }

    /**
     * Get device info
     * @param string $applicationId Application ID
     * @param string $deviceEui Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function device(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceRequest($applicationId, $deviceEui));
    }
}
