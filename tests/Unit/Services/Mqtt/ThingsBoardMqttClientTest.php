<?php

namespace Tests\Unit\Services\Mqtt;

use App\Models\Device;
use App\Services\Mqtt\ThingsBoardMessageDto;
use App\Services\Mqtt\ThingsBoardMqttClient;
use Mockery;
use PhpMqtt\Client\MqttClient as PhpMqttClient;

test('can send telemetry data', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('monitoring/test-device/telemetry', json_encode(['data' => 'test']))
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->sendTelemetry('test-device', ['data' => 'test']);
});

test('can send attributes', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('monitoring/test-device/attributes', json_encode(['attr' => 'value']))
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->sendAttributes('test-device', ['attr' => 'value']);
});

test('can subscribe to RPC requests', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('subscribe')
        ->withArgs(function ($topic, $callback, $qos = 1) {
            return $topic === 'monitoring/test-device/rpc/request/+' && is_callable($callback) && $qos === 1;
        })
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->subscribeToRpcRequests(function () {});
});

test('can send heartbeat', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('monitoring/test-device/telemetry', Mockery::on(function ($payload) {
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

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->sendHeartbeat();
});

test('can report status', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('monitoring/test-device/attributes', Mockery::on(function ($payload) {
            $data = json_decode($payload, true);

            return isset($data['status']) &&
                   isset($data['lastStatusUpdate']) &&
                   isset($data['statusMessage']) &&
                   $data['status'] === 'online' &&
                   $data['statusMessage'] === 'test message';
        }))
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->reportStatus('online', 'test message');
});

test('can send RPC response', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('publish')
        ->once()
        ->with('monitoring/test-device/rpc/response/123', json_encode(['result' => 'success']))
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $client->sendRpcResponse('123', ['result' => 'success']);
});

test('can subscribe to RPC with message DTO', function () {
    $device = Mockery::mock(Device::class);
    $device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $device->shouldReceive('getAttribute')
        ->with('name')
        ->andReturn('test-device');
    $device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
        ]);
    $device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'thingsboard_access_token' => 'test-token',
        ]);

    $phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $phpMqttClient->shouldReceive('connect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('disconnect')
        ->andReturn(true);
    $phpMqttClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $phpMqttClient->shouldReceive('subscribe')
        ->withArgs(function ($topic, $callback, $qos = 1) {
            if ($topic !== 'monitoring/test-device/rpc/request/+' || $qos !== 1) {
                return false;
            }

            // Test the callback with a sample RPC message
            $callback(
                'monitoring/test-device/rpc/request/123',
                json_encode([
                    'method' => 'test_method',
                    'params' => ['param1' => 'value1']
                ])
            );

            return true;
        })
        ->andReturn(true);

    $client = new ThingsBoardMqttClient($device, $phpMqttClient);
    $called = false;
    $client->subscribeToRpc(function ($message, $requestId) use (&$called) {
        expect($message)
            ->toBeInstanceOf(ThingsBoardMessageDto::class)
            ->and($message->method)->toBe('test_method')
            ->and($message->params)->toBe(['param1' => 'value1'])
            ->and($requestId)->toBe('123');
        $called = true;
    });

    expect($called)->toBeTrue();
});

afterEach(function () {
    Mockery::close();
});
