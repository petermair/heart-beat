<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServerType>
 */
class ServerTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'interface_class' => 'App\\Services\\Monitoring\\' . fake()->word() . 'Monitor',
            'description' => fake()->sentence(),
            'required_settings' => ['api_endpoint', 'port'],
            'required_credentials' => ['username', 'password'],
        ];
    }

    /**
     * Indicate that this is a ThingsBoard server type.
     */
    public function thingsboard(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'ThingsBoard',
            'interface_class' => 'App\\Services\\Monitoring\\ThingsBoardMonitor',
            'description' => 'ThingsBoard IoT Platform monitoring',
            'required_settings' => ['api_endpoint'],
            'required_credentials' => ['username', 'password'],
        ]);
    }

    /**
     * Indicate that this is a ChirpStack server type.
     */
    public function chirpstack(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'ChirpStack',
            'interface_class' => 'App\\Services\\Monitoring\\ChirpStackMonitor',
            'description' => 'ChirpStack LoRaWAN Network Server monitoring',
            'required_settings' => ['grpc_endpoint', 'api_endpoint'],
            'required_credentials' => ['api_token'],
        ]);
    }
}
