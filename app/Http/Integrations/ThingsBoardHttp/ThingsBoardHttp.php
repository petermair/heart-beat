<?php

namespace App\Http\Integrations\ThingsBoardHttp;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Contracts\Authenticator;

class ThingsBoardHttp extends Connector
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
     * Get device telemetry data
     * @param string $deviceEui Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceTelemetry(string $deviceEui)
    {
        return $this->get("/api/plugins/telemetry/DEVICE/{$deviceEui}/values/timeseries");
    }

    /**
     * Execute RPC call on device
     * @param string $deviceEui Device EUI
     * @param array $data RPC data
     * @return \Saloon\Http\Response Response
     */
    public function deviceRpc(string $deviceEui, array $data)
    {
        return $this->post("/api/plugins/rpc/oneway/{$deviceEui}", $data);
    }

    /**
     * Get device status
     * @param string $deviceEui Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceStatus(string $deviceEui)
    {
        return $this->get("/api/device/{$deviceEui}/status");
    }

    /**
     * Get devices
     * @return \Saloon\Http\Response Response
     */
    public function devices()
    {
        return $this->get("/api/tenant/devices");
    }

    /**
     * Create device
     * @param array $data Device data
     * @return \Saloon\Http\Response Response
     */
    public function createDevice(array $data)
    {
        return $this->post("/api/device", $data);
    }
}
