<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\Monitoring\DeviceMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeviceMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Device $device
    ) {}

    public function handle(DeviceMonitoringService $monitoringService): void
    {
        $monitoringService->monitorDevice($this->device);
    }
}
