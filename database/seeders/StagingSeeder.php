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

        // Create MQTT Brokers
        $mqttBrokers = [
            'ThingsBoard MQTT' => MqttBroker::create([
                'name' => 'ThingsBoard MQTT',
                'host' => 'mqtt.thingsboard.cloud',
                'port' => 1883,
                'username' => 'mqtt_user',
                'password' => Hash::make('mqtt_password'),
                'ssl_enabled' => false,
            ]),
            'ChirpStack MQTT' => MqttBroker::create([
                'name' => 'ChirpStack MQTT',
                'host' => 'mqtt.chirpstack.io',
                'port' => 8883,
                'username' => 'chirpstack_mqtt',
                'password' => Hash::make('chirpstack_password'),
                'ssl_enabled' => true,
            ]),
        ];

        // Create Server Types
        $serverTypes = [
            'ThingsBoard' => ServerType::create([
                'name' => 'ThingsBoard',
                'interface_class' => 'App\Services\ThingsBoard\ThingsBoardService',
                'description' => 'ThingsBoard IoT Platform',
                'required_settings' => ['timeout', 'retry_attempts'],
                'required_credentials' => ['username', 'password'],
            ]),
            'ChirpStack' => ServerType::create([
                'name' => 'ChirpStack',
                'interface_class' => 'App\Services\ChirpStack\ChirpStackService',
                'description' => 'ChirpStack LoRaWAN Network Server',
                'required_settings' => ['timeout', 'retry_attempts'],
                'required_credentials' => ['api_key'],
            ]),
        ];

        // Create Servers
        $servers = [
            'ThingsBoard Cloud' => Server::create([
                'name' => 'ThingsBoard Cloud',
                'server_type_id' => $serverTypes['ThingsBoard']->id,
                'mqtt_broker_id' => $mqttBrokers['ThingsBoard MQTT']->id,
                'url' => 'https://thingsboard.cloud',
                'description' => 'ThingsBoard Cloud Instance',
                'monitoring_interval' => 60,
                'is_active' => true,
                'credentials' => [
                    'username' => 'admin',
                    'password' => Hash::make('admin123'),
                ],
                'settings' => [
                    'timeout' => 30,
                    'retry_attempts' => 3,
                ],
            ]),
            'ChirpStack Cloud' => Server::create([
                'name' => 'ChirpStack Cloud',
                'server_type_id' => $serverTypes['ChirpStack']->id,
                'mqtt_broker_id' => $mqttBrokers['ChirpStack MQTT']->id,
                'url' => 'https://chirpstack.io',
                'description' => 'ChirpStack Cloud Instance',
                'monitoring_interval' => 60,
                'is_active' => true,
                'credentials' => [
                    'api_key' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9',
                ],
                'settings' => [
                    'timeout' => 30,
                    'retry_attempts' => 3,
                ],
            ]),
        ];

        // Create Communication Types
        $communicationTypes = [
            'LoRaWAN' => CommunicationType::create([
                'name' => 'lorawan',
                'label' => 'LoRaWAN',
                'description' => 'Long Range Wide Area Network',
                'is_active' => true,
            ]),
            'WiFi' => CommunicationType::create([
                'name' => 'wifi',
                'label' => 'WiFi',
                'description' => 'Wireless Local Area Network',
                'is_active' => true,
            ]),
        ];

        // Create Devices
        $devices = [];
        for ($i = 1; $i <= 5; $i++) {
            $devices[] = Device::create([
                'name' => "Test Device $i",
                'description' => "Test Device $i Description",
                'thingsboard_server_id' => $servers['ThingsBoard Cloud']->id,
                'chirpstack_server_id' => $servers['ChirpStack Cloud']->id,
                'application_id' => '1',
                'device_profile_id' => '1',
                'device_eui' => sprintf('a81758fffe03%04d', $i),
                'communication_type_id' => $communicationTypes['LoRaWAN']->id,
                'is_active' => true,
                'last_seen_at' => now(),
                'monitoring_enabled' => true,
            ]);
        }

        // Create Test Scenarios
        $testScenarios = [];
        foreach ($devices as $device) {
            $testScenarios[] = TestScenario::create([
                'name' => "Test Scenario for {$device->name}",
                'description' => "Monitoring scenario for {$device->name}",
                'mqtt_device_id' => $device->id,
                'http_device_id' => $device->id,
                'is_active' => true,
                'interval_seconds' => 300, // 5 minutes
                'timeout_seconds' => 30,
                'max_retries' => 3,
                // Initial statistics
                'thingsboard_success_rate_1h' => 95.5,
                'thingsboard_success_rate_24h' => 97.8,
                'thingsboard_messages_count_1h' => 12,
                'thingsboard_messages_count_24h' => 288,
                'thingsboard_status' => 'HEALTHY',
                'chirpstack_success_rate_1h' => 94.2,
                'chirpstack_success_rate_24h' => 96.5,
                'chirpstack_messages_count_1h' => 12,
                'chirpstack_messages_count_24h' => 288,
                'chirpstack_status' => 'HEALTHY',
                'mqtt_success_rate_1h' => 96.8,
                'mqtt_success_rate_24h' => 98.2,
                'mqtt_messages_count_1h' => 12,
                'mqtt_messages_count_24h' => 288,
                'mqtt_status' => 'HEALTHY',
            ]);
        }

        // Create Test Results
        foreach ($testScenarios as $scenario) {
            for ($i = 1; $i <= 10; $i++) {
                $isMqtt = $i <= 7;
                $flowType = $isMqtt ? TestResult::FLOW_TB_MQTT_HEALTH : TestResult::FLOW_TB_HTTP_HEALTH;
                $status = rand(1, 10) > 2 ? TestResult::STATUS_SUCCESS : TestResult::STATUS_FAILURE;
                TestResult::create([
                    'test_scenario_id' => $scenario->id,
                    'device_id' => $isMqtt ? $scenario->mqtt_device_id : $scenario->http_device_id,
                    'flow_type' => $flowType,
                    'status' => $status,
                    'error_message' => null,
                    'start_time' => now()->subMinutes($i * 5),
                    'end_time' => now()->subMinutes($i * 5)->addSeconds(rand(1, 5)),
                    'execution_time_ms' => rand(100, 5000),
                ]);
            }
        }

        // Create Device Monitoring Results
        foreach ($devices as $device) {
            for ($i = 1; $i <= 10; $i++) {
                DeviceMonitoringResult::create([
                    'device_id' => $device->id,
                    'test_scenario_id' => $testScenarios[array_rand($testScenarios)]->id,
                    'success' => rand(1, 10) > 2, // 80% success rate
                    'error_message' => null,
                    'response_time_ms' => rand(100, 5000),
                    'metadata' => [
                        'flow_number' => rand(1, 9),
                        'timestamp' => now()->subMinutes($i * 5)->timestamp,
                        'counter' => $i,
                    ],
                ]);
            }
        }

        // Create Health Checks
        foreach ($servers as $server) {
            for ($i = 1; $i <= 5; $i++) {
                $isHealthy = rand(1, 10) > 2;
                HealthCheck::create([
                    'server_id' => $server->id,
                    'status' => $isHealthy ? 'HEALTHY' : 'UNHEALTHY',
                    'response_time' => $isHealthy ? rand(50, 500) : null,
                    'error_message' => $isHealthy ? null : 'Connection timeout',
                    'checked_at' => now()->subMinutes($i * 5),
                ]);
            }
        }

        // Create Alert Rules
        foreach ($servers as $server) {
            AlertRule::create([
                'server_id' => $server->id,
                'name' => "Response Time Alert for {$server->name}",
                'description' => 'Alert when response time exceeds threshold',
                'conditions' => [
                    [
                        'metric' => 'response_time',
                        'operator' => '>',
                        'value' => 1000,
                    ],
                ],
                'actions' => [
                    [
                        'type' => 'email',
                        'to' => 'admin@example.com',
                        'subject' => 'High Response Time Alert',
                    ],
                ],
                'is_active' => true,
            ]);
        }

        // Create Notification Types
        NotificationType::create([
            'name' => 'email',
            'display_name' => 'Email',
            'description' => 'Email notifications',
            'configuration_schema' => [
                'type' => 'object',
                'properties' => [
                    'to' => [
                        'type' => 'string',
                        'format' => 'email',
                        'title' => 'Recipient Email',
                    ],
                    'subject' => [
                        'type' => 'string',
                        'title' => 'Email Subject',
                    ],
                ],
                'required' => ['to'],
            ],
            'is_active' => true,
        ]);

        NotificationType::create([
            'name' => 'webhook',
            'display_name' => 'Webhook',
            'description' => 'HTTP webhook notifications',
            'configuration_schema' => [
                'type' => 'object',
                'properties' => [
                    'url' => [
                        'type' => 'string',
                        'format' => 'uri',
                        'title' => 'Webhook URL',
                    ],
                    'method' => [
                        'type' => 'string',
                        'enum' => ['POST', 'PUT'],
                        'default' => 'POST',
                        'title' => 'HTTP Method',
                    ],
                    'headers' => [
                        'type' => 'object',
                        'title' => 'HTTP Headers',
                    ],
                ],
                'required' => ['url'],
            ],
            'is_active' => true,
        ]);

        // Create Notification Settings
        foreach (NotificationType::all() as $type) {
            NotificationSetting::create([
                'notification_type_id' => $type->id,
                'name' => "{$type->display_name} Settings",
                'description' => "Default settings for {$type->display_name} notifications",
                'configuration' => $type->name === 'email' ? [
                    'to' => ['admin@example.com'],
                    'subject' => 'Test Notification',
                ] : [
                    'url' => 'https://example.com/webhook',
                    'method' => 'POST',
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ],
                'is_active' => true,
            ]);
        }

        // Create Service Failure Patterns
        ServiceFailurePattern::create([
            'service_name' => 'HTTP Request',
            'description' => 'Request timeout pattern',
        ]);

        ServiceFailurePattern::create([
            'service_name' => 'Database',
            'description' => 'Database connection error pattern',
        ]);

        ServiceFailurePattern::create([
            'service_name' => 'MQTT',
            'description' => 'MQTT connection error pattern',
        ]);

        // Create Service Failure Flows
        foreach (ServiceFailurePattern::all() as $pattern) {
            ServiceFailureFlow::create([
                'pattern_id' => $pattern->id,
                'flow_number' => rand(1, 9),
                'fails' => true,
                'is_optional' => false,
            ]);
        }

        // Create Test Scenario Service Statuses
        foreach ($testScenarios as $scenario) {
            foreach ([
                TestScenarioServiceStatus::SERVICE_THINGSBOARD,
                TestScenarioServiceStatus::SERVICE_CHIRPSTACK,
                TestScenarioServiceStatus::SERVICE_MQTT,
                TestScenarioServiceStatus::SERVICE_LORATX,
                TestScenarioServiceStatus::SERVICE_LORARX,
            ] as $serviceType) {
                TestScenarioServiceStatus::create([
                    'test_scenario_id' => $scenario->id,
                    'service_type' => $serviceType,
                    'status' => TestScenarioServiceStatus::STATUS_HEALTHY,
                    'success_count_1h' => rand(0, 100),
                    'total_count_1h' => 100,
                    'success_rate_1h' => rand(0, 100),
                ]);
            }
        }

        // Create Test Scenario Service Alerts
        foreach ($testScenarios as $scenario) {
            foreach ([
                TestScenarioServiceStatus::SERVICE_THINGSBOARD,
                TestScenarioServiceStatus::SERVICE_CHIRPSTACK,
                TestScenarioServiceStatus::SERVICE_MQTT,
                TestScenarioServiceStatus::SERVICE_LORATX,
                TestScenarioServiceStatus::SERVICE_LORARX,
            ] as $serviceType) {
                if (rand(0, 1)) { // 50% chance of having an alert
                    TestScenarioServiceAlert::create([
                        'test_scenario_id' => $scenario->id,
                        'service_type' => $serviceType,
                        'alert_type' => rand(0, 1) ? 'WARNING' : 'CRITICAL',
                        'status' => rand(0, 1) ? 'ACTIVE' : 'RESOLVED',
                        'message' => 'Service response time exceeds threshold',
                        'triggered_at' => now()->subHours(rand(1, 24)),
                        'resolved_at' => rand(0, 1) ? now() : null,
                        'acknowledged_at' => rand(0, 1) ? now() : null,
                    ]);
                }
            }
        }
    }
}
