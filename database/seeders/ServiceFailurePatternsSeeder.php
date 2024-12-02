<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\ServiceType;

class ServiceFailurePatternsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patterns = [
            [
                'service_name' => ServiceType::THINGSBOARD->label(),
                'description' => 'Failure pattern when ThingsBoard is down',
                'flows' => [
                    1 => ['fails' => true,  'is_optional' => false],
                    2 => ['fails' => true,  'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => true,  'is_optional' => false],
                    5 => ['fails' => true,  'is_optional' => false],
                    6 => ['fails' => true,  'is_optional' => false],
                    7 => ['fails' => false, 'is_optional' => false],
                    8 => ['fails' => true,  'is_optional' => true],
                    9 => ['fails' => false, 'is_optional' => true],
                ],
            ],
            [
                'service_name' => ServiceType::CHIRPSTACK->label(),
                'description' => 'Failure pattern when ChirpStack is down',
                'flows' => [
                    1 => ['fails' => true,  'is_optional' => false],
                    2 => ['fails' => true,  'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => true,  'is_optional' => false],
                    5 => ['fails' => true,  'is_optional' => false],
                    6 => ['fails' => false, 'is_optional' => false],
                    7 => ['fails' => true,  'is_optional' => false],
                    8 => ['fails' => false, 'is_optional' => true],
                    9 => ['fails' => true,  'is_optional' => true],
                ],
            ],
            [
                'service_name' => ServiceType::MQTT_TB->label(),
                'description' => 'Failure pattern when MQTT TB is down',
                'flows' => [
                    1 => ['fails' => true,  'is_optional' => false],
                    2 => ['fails' => true,  'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => false, 'is_optional' => false],
                    5 => ['fails' => true,  'is_optional' => false],
                    6 => ['fails' => true,  'is_optional' => false],
                    7 => ['fails' => false, 'is_optional' => false],
                    8 => ['fails' => false, 'is_optional' => true],
                    9 => ['fails' => false, 'is_optional' => true],
                ],
            ],
            [
                'service_name' => ServiceType::MQTT_CS->label(),
                'description' => 'Failure pattern when MQTT CS is down',
                'flows' => [
                    1 => ['fails' => true,  'is_optional' => false],
                    2 => ['fails' => true,  'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => true,  'is_optional' => false],
                    5 => ['fails' => false, 'is_optional' => false],
                    6 => ['fails' => false, 'is_optional' => false],
                    7 => ['fails' => true,  'is_optional' => false],
                    8 => ['fails' => false, 'is_optional' => true],
                    9 => ['fails' => false, 'is_optional' => true],
                ],
            ],
            [
                'service_name' => ServiceType::LORATX->label(),
                'description' => 'Failure pattern when LoRa TX is down',
                'flows' => [
                    1 => ['fails' => true,  'is_optional' => false],
                    2 => ['fails' => false, 'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => false, 'is_optional' => false],
                    5 => ['fails' => false, 'is_optional' => false],
                    6 => ['fails' => false, 'is_optional' => false],
                    7 => ['fails' => false, 'is_optional' => false],
                    8 => ['fails' => false, 'is_optional' => true],
                    9 => ['fails' => false, 'is_optional' => true],
                ],
            ],
            [
                'service_name' => ServiceType::LORARX->label(),
                'description' => 'Failure pattern when LoRa RX is down',
                'flows' => [
                    1 => ['fails' => false, 'is_optional' => false],
                    2 => ['fails' => true,  'is_optional' => false],
                    3 => ['fails' => true,  'is_optional' => false],
                    4 => ['fails' => false, 'is_optional' => false],
                    5 => ['fails' => false, 'is_optional' => false],
                    6 => ['fails' => false, 'is_optional' => false],
                    7 => ['fails' => false, 'is_optional' => false],
                    8 => ['fails' => false, 'is_optional' => true],
                    9 => ['fails' => false, 'is_optional' => true],
                ],
            ],
        ];

        foreach ($patterns as $patternData) {
            $pattern = \App\Models\ServiceFailurePattern::create([
                'service_name' => $patternData['service_name'],
                'description' => $patternData['description'],
            ]);

            foreach ($patternData['flows'] as $flowNumber => $flowData) {
                \App\Models\ServiceFailureFlow::create([
                    'pattern_id' => $pattern->id,
                    'flow_number' => $flowNumber,
                    'fails' => $flowData['fails'],
                    'is_optional' => $flowData['is_optional'],
                ]);
            }
        }
    }
}
