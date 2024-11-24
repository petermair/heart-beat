<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\Mqtt\ChirpStackMqttClient;
use App\Services\Mqtt\MqttMonitor;
use App\Services\Mqtt\ThingsBoardMqttClient;
use Illuminate\Console\Command;

class MonitorMqttCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:monitor {device_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start monitoring MQTT messages between ThingsBoard and ChirpStack';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deviceId = $this->argument('device_id');
        $device = Device::findOrFail($deviceId);

        if (! $device->is_active || ! $device->monitoring_enabled) {
            $this->error('Device is not active or monitoring is disabled');

            return 1;
        }

        try {
            $tbClient = new ThingsBoardMqttClient($device);
            $csClient = new ChirpStackMqttClient($device);
            $monitor = new MqttMonitor($device, $tbClient, $csClient);

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

            // Keep the script running with proper exit condition
            while (! $monitor->isStopped()) {
                pcntl_signal_dispatch();
                sleep(1);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return 1;
        }
    }
}
