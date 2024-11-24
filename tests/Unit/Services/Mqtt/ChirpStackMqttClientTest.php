<?php

use App\Models\Device;
use App\Services\Mqtt\ChirpStackMqttClient;
use PhpMqtt\Client\MqttClient as PhpMqttClient;


beforeEach(function () {
    $this->device = Mockery::mock(Device::class);
    $this->device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $this->device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
            'application_id' => 'app123',
        ]);
    $this->device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'chirpstack_api_key' => 'test-key',
            'chirpstack_device_eui' => 'a1b2c3d4e5f6',
        ]);

    $this->phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $this->client = new ChirpStackMqttClient($this->device, $this->phpMqttClient);
});

test('can connect to broker', function () {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(false);

    $this->phpMqttClient->shouldReceive('connect')
        ->once()
        ->andReturn(true);

    $this->client->connect();
});

test('can subscribe to uplink messages', function () {
    $deviceEui = 'a1b2c3d4e5f6';
    $applicationId = 'app123';
    $topic = "application/{$applicationId}/device/{$deviceEui}/event/up";

    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $this->phpMqttClient->shouldReceive('subscribe')
        ->once()
        ->with(
            $topic,
            Mockery::type('callable'),
            1
        )
        ->andReturn(true);

    $this->client->subscribe($topic, function ($message) {
        // Callback function
    });
});

test('can handle uplink message', function () {
    $deviceEui = 'a1b2c3d4e5f6';
    $applicationId = 'app123';
    $topic = "application/{$applicationId}/device/{$deviceEui}/event/up";

    $message = [
        'applicationID' => $applicationId,
        'deviceName' => 'test-device',
        'devEUI' => $deviceEui,
        'data' => base64_encode('test data'),
    ];

    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $receivedMessage = null;
    $this->phpMqttClient->shouldReceive('subscribe')
        ->once()
        ->with(
            $topic,
            Mockery::on(function ($cb) use (&$receivedMessage, $message, $topic) {
                $cb($topic, json_encode($message));

                return true;
            }),
            1
        )
        ->andReturn(true);

    $this->client->subscribe($topic, function ($topic, $msg) use (&$receivedMessage) {
        $receivedMessage = json_decode($msg, true);
    });

    expect($receivedMessage)->toBe($message);
});

test('can disconnect from broker', function () {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $this->phpMqttClient->shouldReceive('disconnect')
        ->once()
        ->andReturn(true);

    $this->client->disconnect();
});

afterEach(function () {
    Mockery::close();
});
