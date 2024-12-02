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
use PHPUnit\Event\Code\Test;

class FlowExecutionService
{

        public function __construct(
            private readonly DeviceCommunicationService $deviceCommunicationService
        ) {
            
        }
    public function startTest(TestScenario $testScenario): TestResult
    {
        

        // Create test result
        $testResult = TestResult::create([
            'test_scenario_id' => $testScenario->id,
            'status' => TestResultStatus::PENDING,
            'start_time' => Carbon::now(),
        ]);

        // Create flows 1-7 (always required)
        $requiredFlows = [
            FlowType::TB_TO_CS,      // Flow 1: TB → CS
            FlowType::CS_TO_TB,      // Flow 2: CS → TB
            FlowType::CS_TO_TB_TO_CS,      // Flow 3: Full round trip
            FlowType::DIRECT_TEST_CS_TB,  // Flow 4: Direct CS → TB
            FlowType::DIRECT_TEST_TB_CS,  // Flow 5: Direct TB → CS
            FlowType::TB_MQTT_HEALTH,     // Flow 6: TB MQTT Health
            FlowType::CS_MQTT_HEALTH,     // Flow 7: CS MQTT Health
        ];

        foreach ($requiredFlows as $flow) {
            $flow = MessageFlow::create([
                'test_result_id' => $testResult->id,
                'flow_number' => $flow->value,
                'flow_type' => $flow,
                'description' => $flow->name,
                'status' => TestResultStatus::PENDING,
                'created_at' => Carbon::now(),
            ]);
            $this->executeFlow($flow);
        }

        // Create flows 8-9 if HTTP device exists
        if ($testScenario->httpDevice) {
            $httpFlows = [
                FlowType::TB_HTTP_HEALTH,  // Flow 8: TB HTTP Health
                FlowType::CS_HTTP_HEALTH,  // Flow 9: CS HTTP Health
            ];

            foreach ($httpFlows as $flow) {
                $flow = MessageFlow::create([
                    'test_result_id' => $testResult->id,
                    'flow_number' => $flow->value,
                    'flow_type' => $flow,
                    'description' => $flow->name,
                    'status' => TestResultStatus::PENDING,
                    'created_at' => Carbon::now(),
                ]);
                $this->executeFlow($flow);
            }
        }

        return $testResult;
    }

    public function executeFlow(MessageFlow $flow): void
    {
        try {
            $device = $flow->testResult->testScenario->mqttDevice;
            $flowType = FlowType::from($flow->flow_number);

            // Execute flow based on type
            match($flowType) {
                // Routes to ThingsBoard                
                FlowType::CS_TO_TB_TO_CS,
                FlowType::CS_TO_TB_TO_CS,
                FlowType:: DIRECT_TEST_TB_CS,                
                FlowType::TB_MQTT_HEALTH => 
                    $this->deviceCommunicationService->sendMqttDataToThingsBoard(
                        $device,
                        $flowType,
                        $flow->testResult
                    ),

                // Routes to ChirpStack
                FlowType::CS_TO_TB,
                FlowType::DIRECT_TEST_TB_CS,
                FlowType::CS_MQTT_HEALTH =>
                    $this->deviceCommunicationService->sendMqttDataToChirpStack(
                        $device,
                        $flowType,
                        $flow->testResult
                    ),

                // HTTP Health Checks - ThingsBoard
                FlowType::TB_HTTP_HEALTH =>
                    $this->deviceCommunicationService->sendHttpDataToThingsBoard(
                        $device,
                        $flowType,
                        $flow->testResult
                    ),

                // HTTP Health Checks - ChirpStack
                FlowType::CS_HTTP_HEALTH =>
                    $this->deviceCommunicationService->sendHttpDataToChirpStack(
                        $device,
                        $flowType,
                        $flow->testResult
                    ),
            };
        } catch (\Exception $e) {
            // Just log the error, don't update status
            Log::error('Flow execution failed', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
