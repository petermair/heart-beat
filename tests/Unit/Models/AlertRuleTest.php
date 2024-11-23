<?php

namespace Tests\Unit\Models;

use App\Models\AlertRule;
use App\Models\MqttBroker;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'alert-rules');

beforeEach(function () {
    $this->server = Server::factory()->create();
    $this->mqttBroker = MqttBroker::factory()->create();
    $this->alertRule = AlertRule::factory()->create([
        'name' => 'Test Alert Rule',
        'conditions' => [
            [
                'type' => 'response_time',
                'threshold' => 1000,
                'operator' => '>',
            ],
        ],
        'actions' => [
            [
                'type' => 'email',
                'target' => 'admin@example.com',
            ],
        ],
        'description' => 'Test Description',
        'is_active' => true,
    ]);
});

test('alert rule has required attributes', function () {
    expect($this->alertRule->name)->toBe('Test Alert Rule');
    expect($this->alertRule->conditions)->toEqual([
        [
            'type' => 'response_time',
            'threshold' => 1000,
            'operator' => '>',
        ],
    ]);
    expect($this->alertRule->actions)->toEqual([
        [
            'type' => 'email',
            'target' => 'admin@example.com',
        ],
    ]);
    expect($this->alertRule->description)->toBe('Test Description');
    expect($this->alertRule->is_active)->toBeTrue();
});

test('alert rule belongs to server', function () {
    $alertRule = AlertRule::factory()
        ->forServer($this->server)
        ->create();

    expect($alertRule->server)
        ->toBeInstanceOf(Server::class)
        ->id->toBe($this->server->id);
    expect($alertRule->mqttBroker)->toBeNull();
});

test('alert rule belongs to mqtt broker', function () {
    $alertRule = AlertRule::factory()
        ->forMqttBroker($this->mqttBroker)
        ->create();

    expect($alertRule->mqttBroker)
        ->toBeInstanceOf(MqttBroker::class)
        ->id->toBe($this->mqttBroker->id);
    expect($alertRule->server)->toBeNull();
});

test('server alert rule factory state', function () {
    $alertRule = AlertRule::factory()
        ->forServer()
        ->create();

    expect($alertRule->server_id)->not()->toBeNull();
    expect($alertRule->mqtt_broker_id)->toBeNull();
    expect($alertRule->conditions[0]['type'])->toBe('response_time');
    expect($alertRule->conditions[0]['threshold'])->toBe(1000);
    expect($alertRule->conditions[0]['operator'])->toBe('>');
});

test('mqtt broker alert rule factory state', function () {
    $alertRule = AlertRule::factory()
        ->forMqttBroker()
        ->create();

    expect($alertRule->mqtt_broker_id)->not()->toBeNull();
    expect($alertRule->server_id)->toBeNull();
    expect($alertRule->conditions[0]['type'])->toBe('downtime');
    expect($alertRule->conditions[0]['threshold'])->toBe(300);
    expect($alertRule->conditions[0]['operator'])->toBe('>');
});
