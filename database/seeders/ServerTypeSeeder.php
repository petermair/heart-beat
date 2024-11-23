<?php

namespace Database\Seeders;

use App\Models\ServerType;
use Illuminate\Database\Seeder;

class ServerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $serverTypes = [
            [
                'name' => 'ThingsBoard',
                'description' => 'ThingsBoard IoT Platform',
                'required_credentials' => ['username', 'password'],
                'required_settings' => ['base_url'],
                'monitoring_interface' => \App\Monitoring\ThingsBoardMonitoring::class,
            ],
            [
                'name' => 'ChirpStack',
                'description' => 'ChirpStack Network Server',
                'required_credentials' => ['api_token'],
                'required_settings' => ['base_url', 'tenant_id'],
                'monitoring_interface' => \App\Monitoring\ChirpStackMonitoring::class,
            ],
        ];

        foreach ($serverTypes as $type) {
            ServerType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
