<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServerTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'ThingsBoard',
                'interface_class' => 'App\\Services\\Monitoring\\ThingsBoardMonitor',
                'description' => 'ThingsBoard IoT Platform monitoring',
                'required_settings' => json_encode(['api_endpoint']),
                'required_credentials' => json_encode(['username', 'password']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ChirpStack',
                'interface_class' => 'App\\Services\\Monitoring\\ChirpStackMonitor',
                'description' => 'ChirpStack LoRaWAN Network Server monitoring',
                'required_settings' => json_encode(['grpc_endpoint', 'api_endpoint']),
                'required_credentials' => json_encode(['api_token']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($types as $type) {
            DB::table('server_types')->updateOrInsert(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
