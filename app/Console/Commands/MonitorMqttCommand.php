<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Mqtt\MqttMonitor;
use App\Services\Mqtt\ThingsBoardMqttClient;
use App\Services\Mqtt\ChirpStackMqttClient;

class MonitorMqttCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:monitor 
                          {--thingsboard-host=localhost : ThingsBoard MQTT broker host}
                          {--thingsboard-port=1883 : ThingsBoard MQTT broker port}
                          {--thingsboard-access-token= : ThingsBoard access token}
                          {--chirpstack-host=localhost : ChirpStack MQTT broker host}
                          {--chirpstack-port=1883 : ChirpStack MQTT broker port}
                          {--chirpstack-username= : ChirpStack MQTT username}
                          {--chirpstack-password= : ChirpStack MQTT password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start monitoring MQTT messages between ThingsBoard and ChirpStack';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tbConfig = [
            'host' => $this->option('thingsboard-host'),
            'port' => (int) $this->option('thingsboard-port'),
            'client_id' => 'thingsboard-client-' . uniqid(),
            'access_token' => $this->option('thingsboard-access-token'),
            'clean_session' => true,
            'last_will_topic' => 'v1/devices/me/attributes',
            'last_will_message' => json_encode(['status' => 'offline']),
            'last_will_qos' => 1,
            'last_will_retain' => true
        ];

        $csConfig = [
            'host' => $this->option('chirpstack-host'),
            'port' => (int) $this->option('chirpstack-port'),
            'client_id' => 'chirpstack-client-' . uniqid(),
            'username' => $this->option('chirpstack-username'),
            'password' => $this->option('chirpstack-password'),
            'clean_session' => true
        ];

        // Validate required options
        if (!$tbConfig['access_token']) {
            $this->error('ThingsBoard access token is required');
            return 1;
        }

        if (!$csConfig['username'] || !$csConfig['password']) {
            $this->error('ChirpStack username and password are required');
            return 1;
        }

        try {
            $tbClient = new ThingsBoardMqttClient($tbConfig);
            $csClient = new ChirpStackMqttClient($csConfig);
            $monitor = new MqttMonitor($tbClient, $csClient);

            $this->info('Starting MQTT monitoring...');
            $this->info('Press Ctrl+C to stop');

            // Register signal handler for graceful shutdown
            pcntl_signal(SIGINT, function () use ($monitor) {
                $this->info("\nStopping MQTT monitoring...");
                $monitor->stopMonitoring();
                exit(0);
            });

            // Start monitoring
            $monitor->startMonitoring();

            // Keep the script running
            while (true) {
                pcntl_signal_dispatch();
                sleep(1);
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
