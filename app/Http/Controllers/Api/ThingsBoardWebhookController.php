<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestResult;
use App\Models\DeviceMessage;
use App\Services\MessageFlow\MessageFlowStatusService;
use App\Enums\TestResultStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ThingsBoardWebhookController extends Controller
{
    private MessageFlowStatusService $messageFlowStatusService;

    public function __construct(MessageFlowStatusService $messageFlowStatusService) 
    {
        $this->messageFlowStatusService = $messageFlowStatusService;
    }

    public function handle(Request $request): JsonResponse
    {
        // Validate the webhook payload
        // f001digitalinput1 = flowType
        // f001unsigned4b2 = monitoringResult->id
        // f001unsigned4b3 = sendTime
        $validated = $request->validate([
            'params' => 'required|array',
            'params.f001digitalinput1' => 'required|integer',
            'params.f001unsigned4b2' => 'required|integer',
            'params.f001unsigned4b3' => 'required|integer'
        ]);

        try {
            // Find the test result
            $testResult = TestResult::find($validated['params']['f001unsigned4b2']);
            if (!$testResult) {
                return response()->json(['error' => 'Test result not found'], Response::HTTP_NOT_FOUND);
            }

            // Calculate response time
            $responseTime = time() - $validated['params']['f001unsigned4b3'];
            
            // Update message flow status
            $messageFlow = $testResult->messageFlows()
                ->where('flow_number', $validated['params']['f001digitalinput1'])
                ->first();
                
            if ($messageFlow) {
                $messageFlow->update([
                    'status' => TestResultStatus::SUCCESS,
                    'completed_at' => now(),
                    'response_time_ms' => $responseTime
                ]);

                // Create or update device message record
                DeviceMessage::updateOrCreate(
                    [
                        'message_flow_id' => $messageFlow->id,
                    ],
                    [
                        'device_id' => $messageFlow->testResult->device_id,
                        'source' => 'ThingsBoard',
                        'success' => true,
                        'error_message' => null,
                        'response_time_ms' => $responseTime,
                        'metadata' => json_encode([
                            'params' => $validated['params'],
                        ]),
                    ]
                );
            }
            
            // Process message flows and update service statuses
            $this->messageFlowStatusService->processTestResult($testResult);

            return response()->json(['responseTime' => $responseTime]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    
}
