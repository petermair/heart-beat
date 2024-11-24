<?php

namespace App\Console\Commands;

use App\Jobs\DeviceMonitoringJob;
use App\Models\Device;
use Illuminate\Console\Command;

class MonitorDevicesCommand extends Command
{
    protected $signature = 'devices:monitor {--device-id= : Specific device ID to monitor}';

    protected $description = 'Monitor devices status';

    public function handle(): int
    {
        $deviceId = $this->option('device-id');

        $query = Device::query()
            ->where('monitoring_enabled', true);

        if ($deviceId) {
            $query->where('id', $deviceId);
        }

        $devices = $query->get();

        $this->info("Monitoring {$devices->count()} devices...");

        foreach ($devices as $device) {
            DeviceMonitoringJob::dispatch($device);
            $this->info("Dispatched monitoring job for device: {$device->name}");
        }

        return self::SUCCESS;
    }
}
