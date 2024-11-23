<?php

namespace Tests\Unit\Models;

use App\Models\Server;
use App\Models\ServerType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_has_required_attributes(): void
    {
        $server = Server::factory()->create([
            'name' => 'Test Server',
            'settings' => ['api_endpoint' => 'http://test.com'],
            'credentials' => ['username' => 'test', 'password' => 'secret'],
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        $this->assertEquals('Test Server', $server->name);
        $this->assertEquals(['api_endpoint' => 'http://test.com'], $server->settings);
        $this->assertEquals(['username' => 'test', 'password' => 'secret'], $server->credentials);
        $this->assertEquals('Test Description', $server->description);
        $this->assertTrue($server->is_active);
    }

    public function test_server_belongs_to_server_type(): void
    {
        $serverType = ServerType::factory()->create();
        $server = Server::factory()->create(['server_type_id' => $serverType->id]);

        $this->assertInstanceOf(ServerType::class, $server->serverType);
        $this->assertEquals($serverType->id, $server->serverType->id);
    }

    public function test_chirpstack_factory_state(): void
    {
        $server = Server::factory()->chirpstack()->create();

        $this->assertInstanceOf(ServerType::class, $server->serverType);
        $this->assertEquals('ChirpStack', $server->serverType->name);
        $this->assertArrayHasKey('grpc_endpoint', $server->settings);
        $this->assertArrayHasKey('api_endpoint', $server->settings);
        $this->assertArrayHasKey('api_token', $server->credentials);
    }
}
