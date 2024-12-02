<?php

namespace Database\Seeders;

use App\Models\AlertRule;
use App\Models\CommunicationType;
use App\Models\Device;
use App\Models\DeviceMonitoringResult;
use App\Models\HealthCheck;
use App\Models\MqttBroker;
use App\Models\NotificationSetting;
use App\Models\NotificationType;
use App\Models\Server;
use App\Models\ServerType;
use App\Models\ServiceFailureFlow;
use App\Models\ServiceFailurePattern;
use App\Models\TestResult;
use App\Models\TestScenario;
use App\Models\TestScenarioNotification;
use App\Models\TestScenarioNotificationSetting;
use App\Models\TestScenarioServiceAlert;
use App\Models\TestScenarioServiceStatus;
use App\Enums\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StagingSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate all tables first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TestScenarioServiceAlert::truncate();
        TestScenarioServiceStatus::truncate();
        ServiceFailureFlow::truncate();
        ServiceFailurePattern::truncate();
        NotificationSetting::truncate();
        NotificationType::truncate();
        AlertRule::truncate();
        HealthCheck::truncate();
        DeviceMonitoringResult::truncate();
        TestResult::truncate();
        TestScenario::truncate();
        Device::truncate();
        CommunicationType::truncate();
        Server::truncate();
        ServerType::truncate();
        MqttBroker::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Server Types
        $serverTypes = [
            'ThingsBoard' => ServerType::create([
                'name' => 'ThingsBoard',
                'interface_class' => 'App\\Services\\Monitoring\\ThingsBoardMonitor',
                'description' => 'ThingsBoard IoT Platform monitoring',
                'required_settings' => json_encode(['api_endpoint']),
                'required_credentials' => json_encode(['username', 'password']),
            ]),
            'ChirpStack' => ServerType::create([
                'name' => 'ChirpStack',
                'interface_class' => 'App\\Services\\Monitoring\\ChirpStackMonitor',
                'description' => 'ChirpStack LoRaWAN Network Server monitoring',
                'required_settings' => json_encode(['grpc_endpoint', 'api_endpoint']),
                'required_credentials' => json_encode(['api_token']),
            ]),
        ];

        // Create Notification Types
        NotificationType::create([
            'name' => 'email',
            'display_name' => 'Email',
            'description' => 'Send notifications via email',
            'configuration_schema' => [
                'required' => ['recipients'],
                'properties' => [
                    'recipients' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'format' => 'email',
                        ],
                        'minItems' => 1,
                    ],
                    'min_interval' => [
                        'type' => 'integer',
                        'minimum' => 60,
                        'default' => 300,
                    ],
                ],
            ],
            'is_active' => true,
        ]);

        NotificationType::create([
            'name' => 'webhook',
            'display_name' => 'Webhook',
            'description' => 'Send notifications to a custom webhook',
            'configuration_schema' => [
                'type' => 'object',
                'properties' => [
                    'url' => [
                        'type' => 'string',
                        'format' => 'uri',
                    ],
                    'method' => [
                        'type' => 'string',
                        'enum' => ['GET', 'POST', 'PUT', 'PATCH'],
                        'default' => 'POST',
                    ],
                    'headers' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'string',
                        ],
                    ],
                    'min_interval' => [
                        'type' => 'integer',
                        'minimum' => 60,
                        'default' => 300,
                    ],
                ],
                'required' => ['url'],
            ],
            'is_active' => true,
        ]);

        NotificationType::create([
            'name' => 'slack',
            'display_name' => 'Slack',
            'description' => 'Send notifications to Slack',
            'configuration_schema' => [
                'type' => 'object',
                'properties' => [
                    'webhook_url' => [
                        'type' => 'string',
                        'format' => 'uri',
                    ],
                    'min_interval' => [
                        'type' => 'integer',
                        'minimum' => 60,
                        'default' => 300,
                    ],
                ],
                'required' => ['webhook_url'],
            ],
            'is_active' => true,
        ]);

        // Create Service Failure Patterns
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
            $pattern = ServiceFailurePattern::create([
                'service_name' => $patternData['service_name'],
                'description' => $patternData['description'],
            ]);

            foreach ($patternData['flows'] as $flowNumber => $flowData) {
                ServiceFailureFlow::create([
                    'pattern_id' => $pattern->id,
                    'flow_number' => $flowNumber,
                    'fails' => $flowData['fails'],
                    'is_optional' => $flowData['is_optional'],
                ]);
            }
        }

        // Create Test Scenarios
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

        foreach ($scenarios as $scenarioData) {
            TestScenario::create([
                'name' => $scenarioData['name'],
                'description' => $scenarioData['description'],
                'flow_number' => $scenarioData['flow_number'],
                'dependencies' => $scenarioData['dependencies'],
                'is_active' => true,
                'interval_seconds' => 300,
                'timeout_seconds' => 30,
                'max_retries' => 3,
            ]);
        }
    }
}
