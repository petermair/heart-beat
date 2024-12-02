<?php

namespace App\Services\Monitoring\StatusCalculation;

use App\Models\TestScenarioServiceStatus;
use App\Enums\StatusType;
use Carbon\Carbon;

class DowntimeCalculator implements StatusCalculatorInterface
{
    public function __construct(
        private readonly int $criticalThresholdMinutes = 10
    ) {}

    public function calculate(TestScenarioServiceStatus $status): StatusType
    {
        if (!$status->downtime_started_at) {
            return StatusType::HEALTHY;
        }

        $downtimeMinutes = Carbon::parse($status->downtime_started_at)->diffInMinutes(now());
        
        return $downtimeMinutes >= $this->criticalThresholdMinutes 
            ? StatusType::CRITICAL 
            : StatusType::HEALTHY;
    }
}
