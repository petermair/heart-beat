<?php

namespace Tests\Unit\Services\Mqtt;

use App\Services\Mqtt\MqttMonitor;
use App\Services\Mqtt\ThingsBoardMqttClient;
use App\Services\Mqtt\ChirpStackMqttClient;
use App\Models\MonitoringDevice;
use App\DataTransferObjects\MessagePayloadDto;
use App\DataTransferObjects\MessageRouteDto;
use App\DataTransferObjects\ChirpStackMessageDto;
use App\DataTransferObjects\ThingsBoardMessageDto;
use PhpMqtt\Client\MqttClient as PhpMqttClient;
use Mockery;
use Pest\Expectation;

beforeEach(function() {
    $this->device = Mockery::mock(MonitoringDevice::class);
    $this->device->shouldReceive('getAttribute')
        ->with('type')
        ->andReturn('chirpstack');
    $this->device->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(1);
    $this->device->shouldReceive('getAttribute')
        ->with('settings')
        ->andReturn([
            'host' => 'localhost',
            'port' => 1883,
            'application_id' => 'app123'
        ]);
    $this->device->shouldReceive('getAttribute')
        ->with('credentials')
        ->andReturn([
            'chirpstack_api_key' => 'test-key',
            'chirpstack_device_eui' => 'a1b2c3d4e5f6',
            'thingsboard_access_token' => 'test-token'
        ]);
    $this->device->shouldReceive('getAttribute')
        ->with('thingsboard_server')
        ->andReturn((object)['name' => 'test-thingsboard']);
    
    // Add array access expectations
    $this->device->shouldReceive('offsetExists')
        ->andReturn(true);
    $this->device->shouldReceive('offsetGet')
        ->with('chirpstack_server')
        ->andReturn((object)['name' => 'test-server']);
    $this->device->shouldReceive('offsetGet')
        ->with('thingsboard_server')
        ->andReturn((object)['name' => 'test-thingsboard']);
    $this->device->shouldReceive('offsetGet')
        ->with('credentials')
        ->andReturn([
            'chirpstack_api_key' => 'test-key',
            'chirpstack_device_eui' => 'a1b2c3d4e5f6',
            'thingsboard_access_token' => 'test-token',
            'thingsboard_device_eui' => 'f6e5d4c3b2a1'
        ]);

    $this->phpMqttClient = Mockery::mock(PhpMqttClient::class);
    $this->phpMqttClient->shouldReceive('connect')->andReturn(true);
    $this->phpMqttClient->shouldReceive('disconnect')->andReturn(true);
    $this->phpMqttClient->shouldReceive('subscribe')->andReturn(true);
    $this->phpMqttClient->shouldReceive('publish')->andReturn(true);
    $this->phpMqttClient->shouldReceive('isConnected')->andReturn(true);

    $this->thingsboardClient = Mockery::mock(ThingsBoardMqttClient::class);
    $this->thingsboardClient->shouldReceive('connect')->andReturn(true);
    $this->thingsboardClient->shouldReceive('disconnect')->andReturn(true);
    $this->thingsboardClient->shouldReceive('isConnected')->andReturn(true);
    
    // Update mock expectations for ThingsBoardMqttClient
    $this->thingsboardClient->shouldReceive('sendTelemetry')
        ->withArgs(function($data) {
            return is_array($data) && isset($data['data']) && isset($data['metadata']);
        })
        ->andReturn(true);
    
    $this->thingsboardClient->shouldReceive('subscribeToRpcRequests')
        ->withArgs(function($callback) {
            return is_callable($callback);
        })
        ->andReturn(true);
    
    $this->thingsboardClient->shouldReceive('reportStatus')
        ->withArgs(function($status, $message = null) {
            return is_string($status) && (is_null($message) || is_string($message));
        })
        ->andReturn(true);

    $this->chirpstackClient = Mockery::mock(ChirpStackMqttClient::class);
    $this->chirpstackClient->shouldReceive('connect')->andReturn(true);
    $this->chirpstackClient->shouldReceive('disconnect')->andReturn(true);
    $this->chirpstackClient->shouldReceive('isConnected')->andReturn(true);
    
    $this->monitor = new MqttMonitor(
        $this->device,
        $this->thingsboardClient,
        $this->chirpstackClient
    );
});

test('can start monitoring rx device', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('RX');

    $this->chirpstackClient->shouldReceive('subscribeToUplink')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturn(true);

    $this->monitor->startMonitoring();
});

test('can handle uplink message', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('RX');

    $deviceEui = 'a1b2c3d4e5f6';
    $applicationId = 'app123';
    
    $message = [
        'deviceInfo' => [
            'applicationID' => $applicationId,
            'deviceName' => 'test-device',
            'devEui' => $deviceEui,
        ],
        'data' => base64_encode('test data'),
        'rxInfo' => [
            [
                'rssi' => -80,
                'snr' => 5.5
            ]
        ],
        'object' => [
            'temperature' => 25.5,
            'humidity' => 60
        ]
    ];

    $this->chirpstackClient->shouldReceive('subscribeToUplink')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(function($callback) use ($message, $applicationId, $deviceEui) {
            $messageDto = ChirpStackMessageDto::fromUplink($message);
            $callback($messageDto, "application/$applicationId/device/$deviceEui/event/up");
            return true;
        });

    $expectedData = [
        'data' => $message['object'],
        'metadata' => [
            'deviceEUI' => $deviceEui,
            'rssi' => -80,
            'snr' => 5.5
        ]
    ];

    $this->thingsboardClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->thingsboardClient->shouldReceive('sendTelemetry')
        ->once()
        ->with($expectedData)
        ->andReturn(true);

    $this->monitor->startMonitoring();
});

test('handles thingsboard telemetry error gracefully', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('RX');

    $deviceEui = 'a1b2c3d4e5f6';
    $applicationId = 'app123';
    
    $message = [
        'deviceInfo' => [
            'applicationID' => $applicationId,
            'deviceName' => 'test-device',
            'devEui' => $deviceEui,
        ],
        'data' => base64_encode('test data'),
        'rxInfo' => [
            [
                'rssi' => -80,
                'snr' => 5.5
            ]
        ],
        'object' => [
            'temperature' => 25.5,
            'humidity' => 60
        ]
    ];

    $this->chirpstackClient->shouldReceive('subscribeToUplink')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(function($callback) use ($message, $applicationId, $deviceEui) {
            $messageDto = ChirpStackMessageDto::fromUplink($message);
            $callback($messageDto, "application/$applicationId/device/$deviceEui/event/up");
            return true;
        });

    $this->thingsboardClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->thingsboardClient->shouldReceive('sendTelemetry')
        ->once()
        ->andThrow(new \Exception('Failed to send telemetry'));

    $this->monitor->startMonitoring();
});

test('can handle tx device', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('TX');

    $deviceEui = 'a1b2c3d4e5f6';
    $requestId = 'req123';
    
    $request = [
        'method' => 'setValue',
        'params' => [
            'value' => 42
        ]
    ];

    $this->thingsboardClient->shouldReceive('subscribeToRpc')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(function($callback) use ($request, $requestId) {
            $messageDto = ThingsBoardMessageDto::fromRpc($request);
            $callback($messageDto, $requestId);
            return true;
        });

    $this->chirpstackClient->shouldReceive('sendDownlink')
        ->once()
        ->with(
            $deviceEui,
            $request['params'],
            2,
            true
        )
        ->andReturn(true);

    $this->thingsboardClient->shouldReceive('sendRpcResponse')
        ->once()
        ->with($requestId, ['success' => true])
        ->andReturn(true);

    $this->monitor->startMonitoring();
});

test('handles rpc error gracefully', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('TX');

    $requestId = 'req123';
    
    $request = [
        'method' => 'setValue',
        'params' => [
            'value' => 42
        ]
    ];

    $this->thingsboardClient->shouldReceive('subscribeToRpc')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(function($callback) use ($request, $requestId) {
            $messageDto = ThingsBoardMessageDto::fromRpc($request);
            $callback($messageDto, $requestId);
            return true;
        });

    $this->chirpstackClient->shouldReceive('sendDownlink')
        ->once()
        ->andThrow(new \Exception('Failed to send downlink'));

    $this->thingsboardClient->shouldReceive('sendRpcResponse')
        ->once()
        ->with($requestId, [
            'success' => false,
            'error' => 'Failed to send downlink'
        ])
        ->andReturn(true);

    $this->monitor->startMonitoring();
});

test('can handle health monitoring', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('HEALTH');

    $this->chirpstackClient->shouldReceive('subscribeToJoin')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(function($callback) {
            $callback(['devAddr' => 'test123']);
            return true;
        });

    $this->thingsboardClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->thingsboardClient->shouldReceive('reportDeviceStatus')
        ->once()
        ->with('online', Mockery::type('array'))
        ->andReturn(true);

    $this->monitor->startMonitoring();
});

test('throws exception for invalid device type', function() {
    $this->device->shouldReceive('getAttribute')
        ->with('device_type')
        ->andReturn('INVALID');

    expect(fn() => $this->monitor->startMonitoring())
        ->toThrow(\RuntimeException::class, 'Unknown device type: INVALID');
});

test('can stop monitoring', function() {
    $this->chirpstackClient->shouldReceive('disconnect')
        ->once()
        ->andReturn(true);

    $this->thingsboardClient->shouldReceive('isConnected')
        ->once()
        ->andReturn(true);
    $this->thingsboardClient->shouldReceive('disconnect')
        ->once()
        ->andReturn(true);

    expect($this->monitor->stopMonitoring())->toBeTrue();
});

afterEach(function() {
    Mockery::close();
});
