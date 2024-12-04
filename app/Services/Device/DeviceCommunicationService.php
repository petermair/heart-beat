<?php

namespace App\Services\Device;

use App\Enums\FlowType;
use App\Enums\MessageFormat;
use App\Http\Integrations\ChirpStackHttp\ChirpStackHttp;
use App\Http\Integrations\ThingsBoardHttp\ThingsBoardHttp;
use App\Models\CommunicationType;
use App\Models\Device;
use App\Models\DeviceMonitoringResult;
use App\Models\TestResult;
use App\Services\Mqtt\ChirpStackMqttClient;
use App\Services\Mqtt\ThingsBoardMqttClient;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Event\Code\Test;

class DeviceCommunicationService
{
   


/**
 * Send MQTT data to ThingsBoard in JSON format
 *
 * @param  Device  $device  The device to send to 
 * @param  FlowType  $flowType  Type of flow 
 * @param  DeviceMonitoringResult  $monitoringResult  Monitoring result to update
 * @return DeviceMonitoringResult
 */
public function sendMqttDataToThingsboard(
    Device $device,    
    FlowType $flowType,    
    TestResult $testResult
): ?Carbon {
    try {
        $startTime = Carbon::now();

        // Prepare MQTT message
        $message = [
            'f001digitalinput1' => $flowType->value,
            'f001unsigned4b2' => $testResult->id,
            'f001unsigned4b3' => time(),
        ];

        $mqttClient = new ThingsBoardMqttClient($device);

        // Send MQTT message
        $mqttClient->connect();
        $mqttClient->sendTelemetry($message);

        

        return $startTime;
        

        throw new Exception('MQTT client not initialized');
    } catch (Exception $e) {    
        Log::error('MQTT communication error: ' . $e->getMessage(), [
            'device_id' => $device->id,            
            'payload' => $mqttPayload ?? null,
            'error' => $e->getMessage(),
        ]);   
        return null;
    }
}


private function generateLPP($flowType, $testResult)     {
    $buffer = '';
                
    // Channel 1: flow_number (1 byte)
    $buffer .= chr(1);                    // Channel
    $buffer .= chr(0x00);                 // Type (Digital Input)
    $buffer .= chr($flowType->flow_number);     // Value

    // Channel 2: monitoring result ID (4 bytes)
    $buffer .= chr(2);                    // Channel
    $buffer .= chr(0xFE);                 // Type (Unsigned 4B)
    $buffer .= pack('N', $testResult->id); // Value (big-endian)

    // Channel 3: timestamp (4 bytes)
    $buffer .= chr(3);                    // Channel
    $buffer .= chr(0xFE);                 // Type (Unsigned 4B)
    $buffer .= pack('N', time()); // Value (big-endian)

    $mqttPayload = [
        'data' => base64_encode($buffer),
        'fPort' => 1,
    ];
    return $mqttPayload;
}

    public function sendMqttDataToChirpStack(
        Device $device,        
        FlowType $flowType,        
        TestResult $testResult
    ): ?Carbon {
        try {
            $startTime = microtime(true);

            // Prepare MQTT payload based on format
            
                // LPP format
                $mqttPayload = $this->generateLPP($flowType, $testResult);
            

            // Get or create MQTT client
            $mqttClient = new ChirpStackMqttClient($device);

            // Send MQTT message
            $mqttClient->connect();
            $mqttClient->sendDownlink(
                $device->device_eui,
                $mqttPayload,
            );

            
            return $startTime;
        } catch (\Exception $e) {
            Log::error('MQTT communication error: ' . $e->getMessage(), [
                'device_id' => $device->id,                
                'payload' => $mqttPayload ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function sendHttpDataToChirpStack(
        Device $device,    
        FlowType $flowType,    
        TestResult $testResult
    ): ?Carbon {
        try {
            $startTime = Carbon::now();

            // Prepare HTTP request parameters
            $requestParams = [
                'f001digitalinput1' => $flowType->value,
                'f001unsigned4b2' => $testResult->id,
                'f001unsigned4b3' => time(),
            ];

            // Send HTTP request
            $httpClient = new ChirpStackHttp($device);
            $response = $httpClient->deviceMessage($device->application_id, $device->device_eui, $requestParams);

            if ($response->successful()) {
                return $startTime;
            }

            throw new \Exception('HTTP request failed with status code: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('HTTP communication error: ' . $e->getMessage(), [
                'device_id' => $device->id,                
                'params' => $requestParams ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function sendHttpDataToThingsBoard(
        Device $device,    
        FlowType $flowType,    
        TestResult $testResult
    ): ?Carbon {
        try {
            $startTime = Carbon::now();

            // Prepare HTTP request parameters
            $requestParams = [
                'f001digitalinput1' => $flowType->value,
                'f001unsigned4b2' => $testResult->id,
                'f001unsigned4b3' => time(),
            ];

            // Send HTTP request
            $httpClient = new ThingsBoardHttp($device);
            $response = $httpClient->sendTelemetry($device->device_eui, $requestParams);

            if ($response->successful()) {
                return $startTime;
            }

            throw new \Exception('HTTP request failed with status code: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('HTTP communication error: ' . $e->getMessage(), [
                'device_id' => $device->id,                
                'params' => $requestParams ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    
}
