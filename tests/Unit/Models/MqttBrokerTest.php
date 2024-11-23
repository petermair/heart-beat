<?php

namespace Tests\Unit\Models;

use App\Models\MqttBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MqttBrokerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mqtt_broker_has_required_attributes(): void
    {
        $broker = MqttBroker::factory()->create([
            'name' => 'Test Broker',
            'host' => 'mqtt.test.com',
            'port' => 1883,
            'username' => 'test',
            'password' => 'secret',
            'description' => 'Test Description',
            'is_active' => true,
            'use_ssl' => false,
        ]);

        $this->assertEquals('Test Broker', $broker->name);
        $this->assertEquals('mqtt.test.com', $broker->host);
        $this->assertEquals(1883, $broker->port);
        $this->assertEquals('test', $broker->username);
        $this->assertEquals('secret', $broker->password);
        $this->assertEquals('Test Description', $broker->description);
        $this->assertTrue($broker->is_active);
        $this->assertFalse($broker->use_ssl);
    }

    public function test_local_factory_state(): void
    {
        $broker = MqttBroker::factory()->local()->create();

        $this->assertEquals('Local MQTT Broker', $broker->name);
        $this->assertEquals('localhost', $broker->host);
        $this->assertEquals(1883, $broker->port);
        $this->assertNull($broker->username);
        $this->assertNull($broker->password);
        $this->assertFalse($broker->use_ssl);
    }

    public function test_secure_factory_state(): void
    {
        $broker = MqttBroker::factory()->secure()->create();

        $this->assertEquals(8883, $broker->port);
        $this->assertTrue($broker->use_ssl);
    }
}
