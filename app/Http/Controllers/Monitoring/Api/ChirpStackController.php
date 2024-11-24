<?php

namespace App\Http\Controllers\Monitoring\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceMonitoringResult;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ChirpStackController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Validate the webhook payload
        $validated = $request->validate([
            'data' => 'required|string', // base64 encoded LPP data
            'rxInfo.*.rssi' => 'required|numeric',
            'rxInfo.*.snr' => 'required|numeric',
            'fPort' => 'required|integer',
        ]);

        try {
            // Decode base64 LPP data and extract counter
            $lppData = base64_decode($validated['data']);
            // Counter should be in the LPP data
            $counter = $this->extractCounterFromLPP($lppData);

            // Find the original monitoring result
            $monitoringResult = DeviceMonitoringResult::find($counter);
            if (!$monitoringResult) {
                return response()->json(['error' => 'Monitoring result not found'], Response::HTTP_NOT_FOUND);
            }

            // Calculate response time
            $responseTime = time() - ($monitoringResult->metadata['timestamp'] ?? 0);

            // Update the monitoring result
            $monitoringResult->success = true;
            $monitoringResult->response_time_ms = $responseTime;
            $monitoringResult->metadata = array_merge($monitoringResult->metadata ?? [], [
                'rssi' => $validated['rxInfo'][0]['rssi'] ?? 0,
                'snr' => $validated['rxInfo'][0]['snr'] ?? 0,
            ]);
            $monitoringResult->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::channel(config('monitoring.logging.channel'))->error(
                'Error processing ChirpStack webhook',
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

    private function extractCounterFromLPP(string $lppData): int
    {
        $position = 0;
        $dataLength = strlen($lppData);

        while ($position < $dataLength) {
            // Get channel number
            $channel = ord($lppData[$position++]);
            
            // Get data type
            $type = ord($lppData[$position++]);
            
            // If this is channel 2 (counter) and type 0xFE (unsigned 4B)
            if ($channel === 2 && $type === 0xFE) {
                // Extract 4 bytes and unpack as big-endian unsigned long
                $counterBytes = substr($lppData, $position, 4);
                return unpack('N', $counterBytes)[1];
            }
            
            // Skip the value bytes based on type
            switch ($type) {
                case 0x00: // Digital Input
                    $position += 1;
                    break;
                case 0xFE: // Unsigned 4B
                    $position += 4;
                    break;
                default:
                    throw new \RuntimeException("Unknown LPP data type: 0x" . dechex($type));
            }
        }

        throw new \RuntimeException("Counter (channel 2) not found in LPP data");
    }
}
