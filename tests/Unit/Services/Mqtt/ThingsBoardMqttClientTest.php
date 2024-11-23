<?php

use App\Services\Mqtt\ThingsBoardMqttClient;
use App\Models\MonitoringDevice;
use PhpMqtt\Client\MqttClient as PhpMqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Mockery;

beforeEach(function() {
    $this->device = Mockery::mock(MonitoringDevice::class);
    $this->device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $this->device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883
        ]);
    $this->device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token'
        ]);

    $this->phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $this->phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('subscribe')
        ->withArgs(function($topic, $callback, $qos = 1) {
            return $topic === 'v1/devices/me/rpc/request/+' && is_callable($callback) && $qos === 1;
        })
        ->andReturn(true);

    $this->client = new ThingsBoardMqttClient($this->device, $this->phpMqttClient);
});

test('can send telemetry data', function() {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('v1/devices/me/telemetry', json_encode(['data' => 'test']))
        ->andReturn(true);

    $this->client->sendTelemetry(['data' => 'test']);
});

test('can send attributes', function() {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('v1/devices/me/attributes', json_encode(['attr' => 'value']))
        ->andReturn(true);

    $this->client->sendAttributes(['attr' => 'value']);
});

test('can subscribe to RPC requests', function() {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('subscribe')
        ->withArgs(function($topic, $callback, $qos = 1) {
            return $topic === 'v1/devices/me/rpc/request/+' && is_callable($callback) && $qos === 1;
        })
        ->andReturn(true);

    $this->client->subscribeToRpcRequests(function() {});
});

test('can send heartbeat', function() {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('v1/devices/me/telemetry', Mockery::on(function($payload) {
            $data = json_decode($payload, true);
            return isset($data['timestamp']) && 
                   isset($data['status']) && 
                   isset($data['device_id']) && 
                   isset($data['type']) &&
                   $data['status'] === 'online' &&
                   $data['device_id'] === 1 &&
                   $data['type'] === 'heartbeat';
        }))
        ->andReturn(true);

    $this->client->sendHeartbeat();
});

test('can report status', function() {
    $this->phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('v1/devices/me/attributes', Mockery::on(function($payload) {
            $data = json_decode($payload, true);
            return isset($data['status']) && 
                   isset($data['lastStatusUpdate']) && 
                   isset($data['statusMessage']) &&
                   $data['status'] === 'online' &&
                   $data['statusMessage'] === 'test message';
        }))
        ->andReturn(true);

    $this->client->reportStatus('online', 'test message');
});

afterEach(function() {
    Mockery::close();
});
