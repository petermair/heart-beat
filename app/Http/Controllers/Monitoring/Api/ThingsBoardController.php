<?php

namespace App\Http\Controllers\Monitoring\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceMonitoringResult;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ThingsBoardController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Validate the webhook payload
        $validated = $request->validate([
            'data' => 'required|array',
            'data.counter' => 'required|integer',
        ]);

        try {
            // Find the original monitoring result using counter from JSON data
            $monitoringResult = DeviceMonitoringResult::find($validated['data']['counter']);
            if (!$monitoringResult) {
                return response()->json(['error' => 'Monitoring result not found'], Response::HTTP_NOT_FOUND);
            }

            // Calculate response time
            $responseTime = time() - ($monitoringResult->metadata['timestamp'] ?? 0);

            // Update the monitoring result
            $monitoringResult->success = true;
            $monitoringResult->response_time_ms = $responseTime;
            $monitoringResult->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error processing ThingsBoard webhook',
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            if (isset($monitoringResult)) {
                $monitoringResult->success = false;
                $monitoringResult->error_message = $e->getMessage();
                $monitoringResult->save();
            }

            return response()->json(
                ['error' => 'Failed to process webhook'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
