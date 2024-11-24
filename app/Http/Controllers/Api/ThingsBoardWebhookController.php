<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mqtt\ChirpStackMqttClient;
use App\Services\Mqtt\ThingsBoardMessageDto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThingsBoardWebhookController extends Controller
{
    public function handleRpc(Request $request)
    {
        // Validate the webhook payload
        $validated = $request->validate([
            'deviceName' => 'required|string',
            'deviceId' => 'required|string',
            'method' => 'required|string',
            'params' => 'required|array',
            'requestId' => 'required|string',
        ]);

        // Create message DTO
        $message = ThingsBoardMessageDto::fromRpc([
            'deviceName' => $validated['deviceName'],
            'method' => $validated['method'],
            'params' => $validated['params'],
        ]);

        // Get the device and forward to ChirpStack
        $device = Device::where('thingsboard_device_id', $validated['deviceId'])->first();
        if (!$device) {
            return response()->json(['error' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $chirpstackClient = new ChirpStackMqttClient($device);
            $chirpstackClient->connect();
            $chirpstackClient->sendDownlink(
                $device->credentials['chirpstack_device_eui'],
                $message->params,
                2, // Use fPort 2 for commands
                true // Require confirmation
            );
            $chirpstackClient->disconnect();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            report($e);
            return response()->json(
                ['error' => 'Failed to forward message to ChirpStack'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
