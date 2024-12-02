<?php

namespace App\Services\Monitoring\StatusCalculation;

use App\Models\TestScenarioServiceStatus;
use App\Enums\StatusType;

interface StatusCalculatorInterface
{
    /**
     * Calculate the status based on service metrics
     */
    public function calculate(TestScenarioServiceStatus $status): StatusType;
}
