# HTTP Monitoring Flows

This document describes the HTTP-based monitoring flows supported by the Heart Beat service. These flows are designed to test and verify device communication using HTTP protocols.

## Available Flows

### Flow 8: JSON to JSON Communication
- **Flow Number**: 8
- **Input Format**: JSON
- **Output Format**: JSON
- **Description**: Tests device communication using JSON format for both request and response.

#### Flow Details
1. Creates a monitoring result record
2. Sends HTTP request with JSON payload:
   ```json
   {
     "f001digitalinput1": 8,
     "f001unsigned4b2": [monitoring_result_id],
     "f001unsigned4b3": [timestamp]
   }
   ```
3. Expects JSON response
4. Updates monitoring result with response data

#### Example Usage
```php
$scenario = TestScenario::where('flow_number', 8)->first();
$service = new TestExecutionService();
$result = $service->executeHttpFlow8($scenario);
```

### Flow 9: JSON to LPP Communication
- **Flow Number**: 9
- **Input Format**: JSON
- **Output Format**: LPP (Cayenne Low Power Payload)
- **Description**: Tests device communication using JSON for requests and LPP format for responses.

#### Flow Details
1. Creates a monitoring result record
2. Sends HTTP request with JSON payload:
   ```json
   {
     "f001digitalinput1": 9,
     "f001unsigned4b2": [monitoring_result_id],
     "f001unsigned4b3": [timestamp]
   }
   ```
3. Expects LPP format response
4. Updates monitoring result with response data

#### Example Usage
```php
$scenario = TestScenario::where('flow_number', 9)->first();
$service = new TestExecutionService();
$result = $service->executeHttpFlow9($scenario);
```

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
        'counter' => counter_value,
        'timestamp' => timestamp,
        'format' => 'json->json' or 'json->lpp',
        'status_code' => http_status_code
    ]
]
```

## Error Handling

All flows include comprehensive error handling:
1. Device configuration validation
2. Request/response validation
3. Detailed error logging
4. Exception handling with stack traces

## Configuration

HTTP flows require proper server configuration in the database. This is managed through the `servers` table:

1. Each device is associated with a server (ThingsBoard or ChirpStack)
2. Each server has the following configuration:
   ```php
   [
       'url' => 'https://thingsboard.example.com',  // Base URL for the server
       'credentials' => [
           'username' => 'admin',           // For ThingsBoard
           'password' => 'your-password',   // For ThingsBoard
           'api_key' => 'your-api-key'     // For ChirpStack
       ],
       'settings' => [
           // Additional server-specific settings
           'timeout' => 30,
           'retry_attempts' => 3
       ]
   ]
   ```

### ThingsBoard Configuration
For ThingsBoard devices:
- The server configuration is linked through `thingsboard_server_id`
- Authentication uses username/password credentials
- Default endpoints follow ThingsBoard API patterns (e.g., `/api/v1/telemetry`)

### ChirpStack Configuration
For ChirpStack devices:
- The server configuration is linked through `chirpstack_server_id`
- Authentication uses API key
- Endpoints follow ChirpStack API patterns (e.g., `/api/applications/{id}/devices/{eui}`)

### Setting Up a New Device
1. Create a server configuration with appropriate URL and credentials
2. Create a device and link it to the server
3. Configure device-specific settings in the server's configuration

## Best Practices

1. Always validate device configuration before executing flows
2. Monitor response times and status codes
3. Implement proper error handling
4. Keep monitoring results for analysis
5. Regular testing of all flows
