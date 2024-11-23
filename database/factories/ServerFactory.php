<?php

namespace Database\Factories;

use App\Models\ServerType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'server_type_id' => ServerType::factory(),
            'url' => fake()->url(),
            'settings' => [
                'api_endpoint' => fake()->url(),
                'port' => fake()->numberBetween(1000, 9999),
            ],
            'credentials' => [
                'username' => fake()->userName(),
                'password' => fake()->password(),
            ],
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
        ];
    }

    /**
     * Configure the model factory to create a ThingsBoard server.
     */
    public function thingsboard(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'server_type_id' => ServerType::factory()->thingsboard(),
                'url' => 'http://thingsboard.example.com',
                'settings' => [
                    'api_endpoint' => 'http://thingsboard.example.com',
                ],
                'credentials' => [
                    'username' => 'tenant@thingsboard.org',
                    'password' => 'tenant',
                ],
            ];
        });
    }

    /**
     * Configure the model factory to create a ChirpStack server.
     */
    public function chirpstack(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'server_type_id' => ServerType::factory()->chirpstack(),
                'url' => 'http://chirpstack.example.com',
                'settings' => [
                    'grpc_endpoint' => 'chirpstack.example.com:8080',
                    'api_endpoint' => 'http://chirpstack.example.com',
                ],
                'credentials' => [
                    'api_token' => fake()->uuid(),
                ],
            ];
        });
    }
}
