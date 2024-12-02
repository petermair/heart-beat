<?php

namespace Database\Seeders;

use App\Models\TestScenario;
use Illuminate\Database\Seeder;

class TestScenarioSeeder extends Seeder
{
    public function run()
    {
        $scenarios = [
            [
                'name' => 'Full Route 1 (TB → CS)',
                'description' => 'ThingsBoard → MQTT TB → LoraTX → MQTT CS → ChirpStack',
                'flow_number' => 1,
                'dependencies' => [
                    'thingsboard' => true,
                    'mqtt_tb' => true,
                    'lora_tx' => true,
                    'mqtt_cs' => true,
                    'chirpstack' => true,
                ],
            ],
            [
                'name' => 'One Way Route (CS → TB)',
                'description' => 'ChirpStack → MQTT CS → LoraRX → MQTT TB → ThingsBoard',
                'flow_number' => 2,
                'dependencies' => [
                    'chirpstack' => true,
                    'mqtt_cs' => true,
                    'lora_rx' => true,
                    'mqtt_tb' => true,
                    'thingsboard' => true,
                ],
            ],
            [
                'name' => 'Two Way Route',
                'description' => 'Complete round trip testing all components',
                'flow_number' => 3,
                'dependencies' => [
                    'thingsboard' => true,
                    'mqtt_tb' => true,
                    'lora_tx' => true,
                    'lora_rx' => true,
                    'mqtt_cs' => true,
                    'chirpstack' => true,
                ],
            ],
            [
                'name' => 'Direct Test 1 (CS → TB)',
                'description' => 'ChirpStack → MQTT CS → ThingsBoard',
                'flow_number' => 4,
                'dependencies' => [
                    'chirpstack' => true,
                    'mqtt_cs' => true,
                    'thingsboard' => true,
                ],
            ],
            [
                'name' => 'Direct Test 2 (TB → CS)',
                'description' => 'ThingsBoard → MQTT TB → ChirpStack',
                'flow_number' => 5,
                'dependencies' => [
                    'thingsboard' => true,
                    'mqtt_tb' => true,
                    'chirpstack' => true,
                ],
            ],
            [
                'name' => 'TB MQTT Health',
                'description' => 'Direct ThingsBoard MQTT connection test',
                'flow_number' => 6,
                'dependencies' => [
                    'thingsboard' => true,
                    'mqtt_tb' => true,
                ],
            ],
            [
                'name' => 'CS MQTT Health',
                'description' => 'Direct ChirpStack MQTT connection test',
                'flow_number' => 7,
                'dependencies' => [
                    'chirpstack' => true,
                    'mqtt_cs' => true,
                ],
            ],
            [
                'name' => 'TB HTTP Health',
                'description' => 'ThingsBoard HTTP connection test',
                'flow_number' => 8,
                'dependencies' => [
                    'thingsboard' => true,
                ],
            ],
            [
                'name' => 'CS HTTP Health',
                'description' => 'ChirpStack HTTP connection test',
                'flow_number' => 9,
                'dependencies' => [
                    'chirpstack' => true,
                ],
            ],
        ];

        foreach ($scenarios as $scenario) {
            TestScenario::updateOrCreate(
                ['flow_number' => $scenario['flow_number']],
                [
                    'name' => $scenario['name'],
                    'description' => $scenario['description'],
                    'dependencies' => $scenario['dependencies'],
                ]
            );
        }
    }
}
