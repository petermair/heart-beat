# MQTT Monitoring Flows

This document describes the MQTT-based monitoring flows supported by the Heart Beat service. These flows are designed to test and verify device communication using MQTT protocol with various payload formats.

## Available Flows

### Flow 1: JSON to LPP Communication
- **Flow Number**: 1
- **Input Format**: JSON
- **Output Format**: LPP (Cayenne Low Power Payload)
- **Description**: Tests device communication by sending JSON format to ThingsBoard and receiving LPP response.

#### Flow Details
1. Creates a monitoring result record
2. Sends MQTT message with JSON payload:
   ```json
   {
     "f001digitalinput1": 1,
     "f001unsigned4b2": [monitoring_result_id],
     "f001unsigned4b3": [timestamp]
   }
   ```
3. Expects LPP format response
4. Updates monitoring result with response data

### Flow 2: LPP to JSON Communication
- **Flow Number**: 2
- **Input Format**: LPP
- **Output Format**: JSON
- **Description**: Tests device communication by sending LPP format to ChirpStack and receiving JSON response.

#### Flow Details
1. Creates a monitoring result record
2. Sends MQTT message with LPP payload:
   ```json
   {
     "data": "[base64_encoded_lpp_payload]",
     "fPort": 1
   }
   ```
3. Expects JSON response
4. Updates monitoring result with response data

### Flow 3: Two-Way Route Test
- **Flow Number**: 3
- **Format**: Two-way route
- **Description**: Tests bidirectional communication route through ChirpStack.

### Flow 4: Direct Test 1
- **Flow Number**: 4
- **Format**: Direct test
- **Description**: First direct communication test with ChirpStack.

### Flow 5: Direct Test 2
- **Flow Number**: 5
- **Format**: Direct test
- **Description**: Second direct communication test with ThingsBoard.

### Flow 6: Extended Route Test
- **Flow Number**: 6
- **Format**: Extended route
- **Description**: Tests extended communication route.

### Flow 7: Complex Integration Test
- **Flow Number**: 7
- **Format**: Complex integration
- **Description**: Tests complex integration scenarios.

## Monitoring Results

Each flow execution creates a `DeviceMonitoringResult` record with the following information:

```php
[
    'device_id' => $device->id,
    'test_scenario_id' => $scenario->id,
    'success' => true/false,
    'error_message' => null,
    'response_time_ms' => response_time,
    'metadata' => [
        'flow_number' => flow_number,
        'timestamp' => timestamp,
        'format' => 'json->lpp|lpp->json|two-way-route|direct-test-1|direct-test-2|...',
        'counter' => counter_value
    ]
]
```

## LPP Payload Format

The LPP (Cayenne Low Power Payload) format is used in several flows:

```php
[
    'data' => base64_encode($lppPayload),
    'fPort' => 1
]
```

The LPP payload is created with:
- Flow type identifier
- Counter value
- Timestamp

## Error Handling

All flows include comprehensive error handling:
1. Device configuration validation
2. MQTT connection management
3. Payload format validation
4. Detailed error logging with stack traces

## Configuration

MQTT flows require proper broker configuration in the database. This is managed through the `mqtt_brokers` table and relationships:

1. Each device is associated with a server (ThingsBoard or ChirpStack)
2. Each server is associated with an MQTT broker
3. The MQTT broker configuration includes:
   ```php
   [
       'host' => 'mqtt.example.com',
       'port' => 1883,
       'ssl_enabled' => false,
       'credentials' => [
           'username' => 'device_username',
           'password' => 'device_password'
       ]
   ]
   ```

### ThingsBoard Configuration
For ThingsBoard devices:
- The broker configuration is linked through `thingsboard_server_id`
- Authentication uses the server's access token
- Default topics follow ThingsBoard patterns (e.g., `v1/devices/me/telemetry`)

### ChirpStack Configuration
For ChirpStack devices:
- The broker configuration is linked through `chirpstack_server_id`
- Authentication uses the server's API key
- Topics follow ChirpStack patterns (e.g., `application/{id}/device/{eui}/rx`)

### Setting Up a New Device
1. Create an MQTT broker configuration
2. Create a server (ThingsBoard or ChirpStack)
3. Associate the broker with the server
4. Create a device and link it to the server
5. Configure device credentials in the server's configuration

## Best Practices

1. Device Configuration
   - Verify MQTT broker settings
   - Check device credentials
   - Test connection stability

2. Payload Handling
   - Validate payload formats
   - Handle encoding/decoding properly
   - Check message size limits

3. Error Management
   - Implement retry logic
   - Log connection issues
   - Monitor broker status

4. Performance
   - Monitor response times
   - Track message delivery
   - Check QoS levels

## Testing

### Prerequisites
- Active MQTT broker
- Configured device credentials
- Proper network connectivity

### Test Execution
```php
// Execute specific MQTT flow
$scenario = TestScenario::where('flow_number', 1)->first();
$service = new TestExecutionService();
$result = $service->executeMqttFlow($scenario, 1);

// Check results
if ($result->success) {
    echo "Flow executed successfully";
    echo "Response time: " . $result->response_time_ms . "ms";
} else {
    echo "Error: " . $result->error_message;
}
```

## Troubleshooting

### Common Issues

1. Connection Problems
   - Check broker availability
   - Verify credentials
   - Check network connectivity

2. Message Format Errors
   - Validate JSON structure
   - Check LPP encoding
   - Verify payload size

3. Response Timeouts
   - Check broker settings
   - Verify device response time
   - Check network latency

### Debug Tools

1. MQTT Client Tools
   - mosquitto_sub
   - mosquitto_pub
   - MQTT Explorer

2. Monitoring Tools
   - Broker statistics
   - Connection logs
   - Message traces
