<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MqttBroker>
 */
class MqttBrokerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' MQTT Broker',
            'host' => fake()->domainName(),
            'port' => fake()->numberBetween(1883, 8883),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
            'use_ssl' => fake()->boolean(),
        ];
    }

    /**
     * Configure the model factory to create a local MQTT broker.
     */
    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Local MQTT Broker',
            'host' => 'localhost',
            'port' => 1883,
            'username' => null,
            'password' => null,
            'use_ssl' => false,
        ]);
    }

    /**
     * Configure the model factory to create a secure MQTT broker.
     */
    public function secure(): static
    {
        return $this->state(fn (array $attributes) => [
            'port' => 8883,
            'use_ssl' => true,
        ]);
    }
}
