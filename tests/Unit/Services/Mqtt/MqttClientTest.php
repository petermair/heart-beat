<?php

use App\Services\Mqtt\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

beforeEach(function () {
    $this->config = [
        'host' => 'localhost',
        'port' => 1883,
        'client_id' => 'test-client',
        'username' => 'test-user',
        'password' => 'test-pass',
        'last_will_topic' => 'test/lwt',
        'last_will_message' => 'offline',
    ];
    $this->phpMqttClient = Mockery::mock(MqttClient::class);
    $this->client = new MqttClient($this->config, $this->phpMqttClient);
});

test('can create mqtt client', function () {
    $client = new MqttClient($this->config);
    expect($client)->toBeInstanceOf(MqttClient::class);
});

test('can connect to broker', function () {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(false);

    $this->phpMqttClient->shouldReceive('connect')
        ->once()
        ->with(Mockery::type(ConnectionSettings::class), true)
        ->andReturn(true);

    $this->client->connect();
    expect($this->client)->toBeObject();
});

test('can publish message', function () {
    $topic = 'test/topic';
    $message = 'test message';

    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $this->phpMqttClient->shouldReceive('publish')
        ->once()
        ->with($topic, $message, 1, false);

    $this->client->publish($topic, $message);
});

test('can subscribe to topic', function () {
    $topic = 'test/topic';
    $callback = function ($message) {};

    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $this->phpMqttClient->shouldReceive('subscribe')
        ->once()
        ->with($topic, Mockery::type('callable'), 1)
        ->andReturn(true);

    $this->client->subscribe($topic, $callback);
});

test('can disconnect', function () {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    $this->phpMqttClient->shouldReceive('disconnect')
        ->once();

    $this->client->disconnect();
});

afterEach(function () {
    Mockery::close();
});
