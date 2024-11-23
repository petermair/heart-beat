<?php

namespace Database\Factories;

use App\Models\MqttBroker;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlertRule>
 */
class AlertRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'conditions' => [
                [
                    'type' => fake()->randomElement(['downtime', 'response_time', 'error_rate', 'certificate']),
                    'threshold' => fake()->numberBetween(1, 100),
                    'operator' => fake()->randomElement(['>', '<', '>=', '<=', '==']),
                ],
            ],
            'actions' => [
                [
                    'type' => fake()->randomElement(['email', 'slack', 'webhook']),
                    'target' => fake()->email(),
                ],
            ],
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
        ];
    }

    /**
     * Configure the model factory to create a server alert rule.
     */
    public function forServer(Server $server = null): static
    {
        return $this->state(fn (array $attributes) => [
            'server_id' => $server ?? Server::factory(),
            'conditions' => [
                [
                    'type' => 'response_time',
                    'threshold' => 1000, // 1 second
                    'operator' => '>',
                ],
            ],
        ]);
    }

    /**
     * Configure the model factory to create an MQTT broker alert rule.
     */
    public function forMqttBroker(MqttBroker $broker = null): static
    {
        return $this->state(fn (array $attributes) => [
            'mqtt_broker_id' => $broker ?? MqttBroker::factory(),
            'conditions' => [
                [
                    'type' => 'downtime',
                    'threshold' => 300, // 5 minutes
                    'operator' => '>',
                ],
            ],
        ]);
    }
}
