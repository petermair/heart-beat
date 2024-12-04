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

class ChirpStackWebhookController extends Controller
{
    private MessageFlowStatusService $messageFlowStatusService;

    public function __construct(MessageFlowStatusService $messageFlowStatusService) 
    {
        $this->messageFlowStatusService = $messageFlowStatusService;
    }

    public function handle(Request $request): JsonResponse
    {
        // Validate the webhook payload
        $validated = $request->validate([
            'deviceInfo.deviceName' => 'sometimes|string',
            'deviceInfo.devEui' => 'sometimes|string',
            'data' => 'required|string', // base64 encoded
            'fPort' => 'required|integer',
            'rxInfo' => 'sometimes|array',
            'rxInfo.*.rssi' => 'sometimes|numeric',
            'rxInfo.*.snr' => 'sometimes|numeric',
        ]);

        // Only process messages with fPort f001 (61441) for flow validation
        if ($validated['fPort'] !== 61441) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            // Decode base64 data
            $data = base64_decode($validated['data']);
            
            // Extract flow info from LPP format
            // f001digitalinput1 = flowType
            // f001unsigned4b2 = monitoringResult->id
            // f001unsigned4b3 = sendTime
            $flowType = $this->extractDigitalInput(1, $data);
            $testResultId = $this->extractUnsigned4B(2, $data);
            $sendTime = $this->extractUnsigned4B(3, $data);

            // Find the test result
            $testResult = TestResult::find($testResultId);
            if (!$testResult) {
                return response()->json(['error' => 'Test result not found'], Response::HTTP_NOT_FOUND);
            }

            // Calculate response time
            $responseTime = time() - $sendTime;
            
            // Update message flow status
            $messageFlow = $testResult->messageFlows()
                ->where('flow_number', $flowType)
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
                        'source' => 'ChirpStack',
                        'success' => true,
                        'error_message' => null,
                        'response_time_ms' => $responseTime,
                        'metadata' => json_encode([
                            'rssi' => $validated['rxInfo'][0]['rssi'] ?? null,
                            'snr' => $validated['rxInfo'][0]['snr'] ?? null,
                        ]),
                    ]
                );
            }
            
            // Process message flows and update service statuses
            $this->messageFlowStatusService->processTestResult($testResult);

            return response()->json([
                'status' => 'success',
                'responseTime' => $responseTime
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Extract digital input value from LPP data
     */
    private function extractDigitalInput(int $channel, string $data): int
    {
        $position = 0;
        while ($position < strlen($data)) {
            $currentChannel = ord($data[$position++]);
            $type = ord($data[$position++]);
            
            if ($currentChannel === $channel && $type === 0) { // Digital Input
                return ord($data[$position]);
            }
            
            // Skip value based on type
            switch ($type) {
                case 0: // Digital Input
                    $position += 1;
                    break;
                case 0xFE: // Unsigned 4B
                    $position += 4;
                    break;
            }
        }
        throw new \RuntimeException("Digital input channel {$channel} not found");
    }

    /**
     * Extract unsigned 4-byte value from LPP data
     */
    private function extractUnsigned4B(int $channel, string $data): int
    {
        $position = 0;
        while ($position < strlen($data)) {
            $currentChannel = ord($data[$position++]);
            $type = ord($data[$position++]);
            
            if ($currentChannel === $channel && $type === 0xFE) { // Unsigned 4B
                return unpack('N', substr($data, $position, 4))[1];
            }
            
            // Skip value based on type
            switch ($type) {
                case 0: // Digital Input
                    $position += 1;
                    break;
                case 0xFE: // Unsigned 4B
                    $position += 4;
                    break;
            }
        }
        throw new \RuntimeException("Unsigned 4B channel {$channel} not found");
    }
}
