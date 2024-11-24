<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunicationTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'mqtt',
                'label' => 'MQTT',
                'description' => 'Message Queue Telemetry Transport protocol',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'http',
                'label' => 'HTTP',
                'description' => 'Hypertext Transfer Protocol',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($types as $type) {
            DB::table('communication_types')->updateOrInsert(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
