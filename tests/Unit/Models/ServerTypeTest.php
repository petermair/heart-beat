<?php

namespace Tests\Unit\Models;

use App\Models\Server;
use App\Models\ServerType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_type_has_required_attributes(): void
    {
        $serverType = ServerType::factory()->create([
            'name' => 'Test Server',
            'interface_class' => 'App\\Services\\Monitoring\\TestMonitor',
            'description' => 'Test Description',
            'required_settings' => ['api_endpoint'],
            'required_credentials' => ['username', 'password'],
        ]);

        $this->assertEquals('Test Server', $serverType->name);
        $this->assertEquals('App\\Services\\Monitoring\\TestMonitor', $serverType->interface_class);
        $this->assertEquals('Test Description', $serverType->description);
        $this->assertEquals(['api_endpoint'], $serverType->required_settings);
        $this->assertEquals(['username', 'password'], $serverType->required_credentials);
    }

    public function test_server_type_has_many_servers(): void
    {
        $serverType = ServerType::factory()->create();
        Server::factory()->count(3)->create(['server_type_id' => $serverType->id]);

        $this->assertCount(3, $serverType->servers);
        $this->assertInstanceOf(Server::class, $serverType->servers->first());
    }

    public function test_chirpstack_factory_state(): void
    {
        $serverType = ServerType::factory()->chirpstack()->create();

        $this->assertEquals('ChirpStack', $serverType->name);
        $this->assertEquals('App\\Services\\Monitoring\\ChirpStackMonitor', $serverType->interface_class);
        $this->assertContains('grpc_endpoint', $serverType->required_settings);
        $this->assertContains('api_endpoint', $serverType->required_settings);
        $this->assertContains('api_token', $serverType->required_credentials);
    }
}
