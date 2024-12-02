<?php

namespace App\Services\MessageFlow;

use App\Enums\FlowType;
use App\Enums\TestResultStatus;
use App\Models\MessageFlow;
use App\Models\TestResult;
use App\Models\TestScenario;
use App\Services\Device\DeviceCommunicationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MessageFlowService
{
    public function __construct(
        private readonly DeviceCommunicationService $deviceCommunicationService
    ) {}

    
}
