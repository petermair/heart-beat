<?php

namespace App\Http\Integrations\ChirpStackHttp;

use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceMessagesRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceStatusRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\SendDeviceMessageRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\DeviceResponseRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\SimulateUplinkRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\TestMqttConnectionRequest;
use App\Http\Integrations\ChirpStackHttp\Requests\Devices\TestHttpConnectionRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

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
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceMessages(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceMessagesRequest($applicationId, $deviceEui));
    }

    /**
     * Get device status
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function deviceStatus(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceStatusRequest($applicationId, $deviceEui));
    }

    /**
     * Get device info
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function device(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceRequest($applicationId, $deviceEui));
    }

    /**
     * Send device message
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @param  array  $data  Message data
     * @return \Saloon\Http\Response Response
     */
    public function deviceMessage(string $applicationId, string $deviceEui, array $data)
    {
        return $this->send(new SendDeviceMessageRequest($applicationId, $deviceEui, $data));
    }

    /**
     * Get device response
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @param  int  $messageId  Message ID
     * @return \Saloon\Http\Response Response
     */
    public function deviceResponse(string $applicationId, string $deviceEui, int $messageId)
    {
        return $this->send(new DeviceResponseRequest($applicationId, $deviceEui, $messageId));
    }

    /**
     * Get device messages
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function getDeviceMessages(string $applicationId, string $deviceEui)
    {
        return $this->send(new DeviceMessagesRequest($applicationId, $deviceEui));
    }

    /**
     * Simulate uplink
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @param  array  $data  Uplink data
     * @return \Saloon\Http\Response Response
     */
    public function simulateUplink(string $applicationId, string $deviceEui, array $data)
    {
        return $this->send(new SimulateUplinkRequest($applicationId, $deviceEui, $data));
    }

    /**
     * Test MQTT connection
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function testMqttConnection(string $applicationId, string $deviceEui)
    {
        return $this->send(new TestMqttConnectionRequest($applicationId, $deviceEui));
    }

    /**
     * Test HTTP connection
     *
     * @param  string  $applicationId  Application ID
     * @param  string  $deviceEui  Device EUI
     * @return \Saloon\Http\Response Response
     */
    public function testHttpConnection(string $applicationId, string $deviceEui)
    {
        return $this->send(new TestHttpConnectionRequest($applicationId, $deviceEui));
    }
}
