<?php

namespace Database\Seeders;

use App\Models\CommunicationType;
use Illuminate\Database\Seeder;

class CommunicationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'mqtt',
                'label' => 'MQTT',
                'description' => 'Message Queuing Telemetry Transport protocol',
                'is_active' => true,
            ],
            [
                'name' => 'http',
                'label' => 'HTTP',
                'description' => 'Hypertext Transfer Protocol',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            CommunicationType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
