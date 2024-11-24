<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mqtt\ChirpStackMessageDto;
use App\Services\Mqtt\ThingsBoardMqttClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChirpStackWebhookController extends Controller
{
    public function handleUplink(Request $request)
    {
        // Validate the webhook payload
        $validated = $request->validate([
            'deviceInfo.deviceName' => 'required|string',
            'deviceInfo.devEui' => 'required|string',
            'data' => 'required|string', // base64 encoded
            'rxInfo.*.rssi' => 'required|numeric',
            'rxInfo.*.snr' => 'required|numeric',
        ]);

        // Create message DTO
        $message = ChirpStackMessageDto::fromUplink([
            'deviceEui' => $validated['deviceInfo']['devEui'],
            'data' => base64_decode($validated['data']),
            'rssi' => $validated['rxInfo'][0]['rssi'] ?? 0,
            'snr' => $validated['rxInfo'][0]['snr'] ?? 0,
        ]);

        // Get the device and forward to ThingsBoard
        $device = Device::where('device_eui', $message->deviceEui)->first();
        if (!$device) {
            return response()->json(['error' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $thingsboardClient = new ThingsBoardMqttClient($device);
            $thingsboardClient->connect();
            $thingsboardClient->sendTelemetry([
                'data' => $message->data,
                'metadata' => [
                    'deviceEUI' => $message->deviceEui,
                    'rssi' => $message->rssi,
                    'snr' => $message->snr,
                ],
            ]);
            $thingsboardClient->disconnect();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            report($e);
            return response()->json(
                ['error' => 'Failed to forward message to ThingsBoard'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
