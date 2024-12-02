# Message Flow Architecture

## Overview
This document describes the simplified message flow architecture for processing messages from ChirpStack and ThingsBoard.

## Entry Points

### Sending Data
1. **App to ThingsBoard** (`POST /api/v1/[DEVICE_TOKEN]/telemetry`)
   - App sends telemetry data to ThingsBoard
   - JSON format
   - Used in Flow 8

2. **App to ChirpStack** (`MQTT topic: application/[APP_ID]/device/[DEV_EUI]/tx`)
   - App sends commands to ChirpStack
   - LPP format
   - Used in Flow 3, 5

### Receiving Data
1. **ChirpStack to App** (`/api/chirpstack/webhook`)
   - Receives device messages from ChirpStack
   - Updates flow status (Flow 6)
   - LPP format
   - Triggers next steps (Flow 7-9 if HTTP device configured)

2. **ThingsBoard to App** (`/api/thingsboard/webhook`)
   - Receives commands from ThingsBoard
   - JSON format
   - Starts new test (Flow 1)
   - Confirms message receipt (Flow 9)

## Message Flows

### Flow Execution
1. TestExecutionService creates flows 1-7 by default
2. If TestScenario.http_device_id exists, also create flows 8-9
3. Each flow represents a step in the communication chain
4. Webhooks/endpoints only check flow numbers and data format:
   - ThingsBoard endpoint: Expects JSON format
   - ChirpStack endpoint: Expects LPP format

### Flow Definitions
```php
const MESSAGE_FLOWS = [
       // Routing Flows
       case TB_TO_CS = 'TB_TO_CS';                     // 1. Route TB to CS
       case CS_TO_TB = 'CS_TO_TB';                     // 2. Route CS to TB
       case TB_CS_TB = 'TB_CS_TB';                     // 3. Route TB-CS-TB

       // Direct Test Flows
       case DIRECT_TEST_CS_TB = 'DIRECT_TEST_CS_TB';   // 4. Direct Test CS to TB
       case DIRECT_TEST_TB_CS = 'DIRECT_TEST_TB_CS';   // 5. Direct Test TB to CS

       // Health Check Flows
       case TB_MQTT_HEALTH = 'TB_MQTT_HEALTH';         // 6. TB MQTT Health
       case CS_MQTT_HEALTH = 'CS_MQTT_HEALTH';         // 7. CS MQTT Health
       case TB_HTTP_HEALTH = 'TB_HTTP_HEALTH';         // 8. TB HTTP Health
       case CS_HTTP_HEALTH = 'CS_HTTP_HEALTH';         // 9. CS HTTP Health
];

### Message Formats

For all messages, we track:
- `flowNumber`: Current step in the message flow (1-9)
- `monitoringResultId`: Links message to specific test execution
- `timestamp`: Unix timestamp

#### ThingsBoard Format (JSON)
```php
// Message format
$message = [
    'f001digitalinput1' => $flowType->value,      // Flow number (1-9)
    'f001unsigned4b2' => $monitoringResult->id,   // Test ID
    'f001unsigned4b3' => time(),                  // Timestamp
];
```

#### ChirpStack Format (LPP)
```php
// Binary LPP format
$buffer = '';

// Channel 1: flow_number (1 byte)
$buffer .= chr(1);                    // Channel
$buffer .= chr(0x00);                 // Type (Digital Input)
$buffer .= chr($flowType->value);     // Value (1-9)

// Channel 2: monitoring result ID (4 bytes)
$buffer .= chr(2);                    // Channel
$buffer .= chr(0xFE);                 // Type (Unsigned 4B)
$buffer .= pack('N', $monitoringResult->id); // Value (big-endian)

// Channel 3: timestamp (4 bytes)
$buffer .= chr(3);                    // Channel
$buffer .= chr(0xFE);                 // Type (Unsigned 4B)
$buffer .= pack('N', time());         // Value (big-endian)

// MQTT payload
$mqttPayload = [
    'data' => base64_encode($buffer), // Base64 encoded LPP data
    'fPort' => 1,                     // Port number
];
```

### Flow Tracking and Webhook Processing

#### Flow Status Management
Each message flow is tracked in the database:
```php
MessageFlow::create([
    'test_result_id' => $testResultId,
    'flow_number' => $flowNumber,
    'status' => 'pending'
]);
```

#### Webhook Processing
1. **Receiving Data**
   - ChirpStack webhooks contain LPP encoded data
   - ThingsBoard webhooks contain JSON telemetry data
   
2. **Flow Update**
   ```php
   MessageFlow::where('test_result_id', $testResultId)
       ->where('flow_number', $flowNumber)
       ->update(['status' => 'completed']);
   ```

3. **Test Completion**
   ```php
   $testResult = TestResult::find($testResultId);
   $requiredFlows = $testResult->http_device_id ? range(1, 9) : range(1, 7);
   
   $completed = MessageFlow::where('test_result_id', $testResultId)
       ->whereIn('flow_number', $requiredFlows)
       ->where('status', 'completed')
       ->count() === count($requiredFlows);
   
   if ($completed) {
       $testResult->update(['status' => 'SUCCESS']);
   }
   ```

### Core Services

### MessageFlowService
**Responsibility**: Manage message flows

Functions:
- Complete message flow
- Calculate flow metrics

### DeviceMessageService
**Responsibility**: Process device messages

Functions:
- Process message
- Validate message
- Extract message data

### ServiceStatusService
**Responsibility**: Maintain service status

Functions:
- Update service status
- Calculate service metrics
- Get current service status

### NotificationService
**Responsibility**: Handle notification distribution

Functions:
- Handle status change
- Determine if notification is needed
- Send notification

## Class Structures

### Models

#### MessageFlow
```php
class MessageFlow
{
    public int $id;
    public int $test_result_id;    // Reference to parent TestResult
    public int $flow_number;       // 1-9 (represents each step)
    public string $description;    // Human readable flow description
    public string $status;         // 'pending', 'completed', 'failed'
    public ?string $error_message;
    public DateTime $created_at;
    public DateTime $completed_at;
}
```

#### DeviceMessage
```php
class DeviceMessage
{
    public int $id;
    public int $device_id;
    public int $message_flow_id;        // Reference to MessageFlow
    public string $source;              // Which system sent the message
    public bool $success;               // Whether message was processed successfully
    public string $error_message;
    public int $response_time_ms;
    public array $metadata;             // Message-specific data
    public DateTime $created_at;
}
```

#### ServiceStatus
```php
class ServiceStatus
{
    public int $id;
    public int $test_scenario_id;
    public string $service_type;         // From ServiceType enum
    public string $status;               // From StatusType enum
    public DateTime $last_success_at;
    public DateTime $last_failure_at;
    public int $success_count_1h;
    public int $total_count_1h;
    public float $success_rate_1h;
    public DateTime $downtime_started_at;
    public DateTime $created_at;
    public DateTime $updated_at;
}
```

#### Notification
```php
class Notification
{
    public int $id;
    public int $service_status_id;      // Reference to ServiceStatus
    public string $notification_type_id; // Reference to notification:Type    
    public DateTime $last_sent_at;
    public int $retry_count;
}
```

#### TestResult
```php
class TestResult
{
    public int $id;
    public int $test_scenario_id;
    public string $status;         // PENDING, SUCCESS, FAILURE
    public DateTime $start_time;
    public DateTime $end_time;
    public int $execution_time_ms;
    public ?string $error_message;
}
```

#### TestScenario
```php
class TestScenario
{
    public int $id;
    public int $mqtt_device_id;    // Required - ChirpStack device
    public ?int $http_device_id;   // Optional - ThingsBoard device
    // ... other fields
}
```

### Services

#### MessageFlowService
```php
class MessageFlowService
{
    public function startFlow(string $source, int $flowNumber): MessageFlow;
    public function completeFlow(int $flowId, bool $success): void;
    private function calculateFlowMetrics(MessageFlow $flow): array;
}
```

#### DeviceMessageService
```php
class DeviceMessageService
{
    public function processMessage(string $source, array $payload): DeviceMessage;
    public function validateMessage(array $payload): bool;
    private function extractMessageData(array $payload): array;
}
```

#### ServiceStatusService
```php
class ServiceStatusService
{
    enum StatusType: string
    {
        case HEALTHY = 'HEALTHY';
        case WARNING = 'WARNING';
        case CRITICAL = 'CRITICAL';
    }

    enum ServiceType: string
    {
        case CHIRPSTACK = 'chirpstack';
        case THINGSBOARD = 'thingsboard';
        case MQTT_TB = 'mqtt_tb';
        case MQTT_CS = 'mqtt_cs';
        case LORATX = 'loratx';
        case LORARX = 'lorarx';
    }

    protected function determineStatus(): void
    {
        // Rule 1: Critical if down for 10+ minutes
        if ($this->downtime_started_at && 
            Carbon::parse($this->downtime_started_at)->diffInMinutes(now()) >= 10) {
            $this->status = StatusType::CRITICAL;
            return;
        }

        // Rule 2: Warning if success rate below 90%
        if ($this->success_rate_1h < 90) {
            $this->status = StatusType::WARNING;
            return;
        }

        // Rule 3: Otherwise healthy
        $this->status = StatusType::HEALTHY;
    }

    public function getCurrentStatus(string $service): ServiceStatus
    {
        // Get recent messages (last hour)
        $recentMessages = DeviceMessage::where('source', $service)
            ->where('created_at', '>=', now()->subSeconds(self::RECENT_WINDOW))
            ->get();

        if ($recentMessages->isEmpty()) {
            // No recent messages - check if service has any messages in last 24h
            $hasLongTermMessages = DeviceMessage::where('source', $service)
                ->where('created_at', '>=', now()->subSeconds(self::LONG_WINDOW))
                ->exists();

            return $hasLongTermMessages ? ServiceStatus::WARNING : ServiceStatus::CRITICAL;
        }

        // Calculate success rate
        $successRate = ($recentMessages->where('success', true)->count() / $recentMessages->count()) * 100;

        // Determine status based on success rate
        if ($successRate >= 90) {
            return ServiceStatus::HEALTHY;
        } elseif ($successRate >= 80) {
            return ServiceStatus::WARNING;
        } else {
            return ServiceStatus::CRITICAL;
        }
    }

    public function updateServiceMetrics(string $service): array
    {
        $now = now();
        $hourAgo = $now->copy()->subHour();
        $dayAgo = $now->copy()->subDay();

        // Get messages for different time windows
        $hourlyMessages = DeviceMessage::where('source', $service)
            ->whereBetween('created_at', [$hourAgo, $now])
            ->get();

        $dailyMessages = DeviceMessage::where('source', $service)
            ->whereBetween('created_at', [$dayAgo, $now])
            ->get();

        // Calculate metrics
        return [
            'success_rate_1h' => $this->calculateSuccessRate($hourlyMessages),
            'success_rate_24h' => $this->calculateSuccessRate($dailyMessages),
            'last_success_at' => DeviceMessage::where('source', $service)
                ->where('success', true)
                ->latest()
                ->value('created_at'),
            'last_failure_at' => DeviceMessage::where('source', $service)
                ->where('success', false)
                ->latest()
                ->value('created_at'),
            'message_count_1h' => $hourlyMessages->count(),
            'message_count_24h' => $dailyMessages->count(),
        ];
    }

    private function calculateSuccessRate(Collection $messages): float
    {
        if ($messages->isEmpty()) {
            return 0.0;
        }

        $successCount = $messages->where('success', true)->count();
        return ($successCount / $messages->count()) * 100;
    }
}
```

#### NotificationService
```php
class NotificationService
{
    public function handleStatusChange(ServiceStatus $status): void;
    private function shouldNotify(ServiceStatus $status): bool;
    private function sendNotification(ServiceStatus $status, array $config): void;
}
```

## Service Metrics

### Available Metrics

1. **Success Rates**
```php
class ServiceMetrics
{
    // Basic Success Rate
    public function calculateSuccessRate(string $service): float
    {
        return ($this->success_count_1h / $this->total_count_1h) * 100;
    }

    // Time-window Success Rates
    public function getSuccessRates(string $service): array
    {
        return [
            '1h' => $this->success_rate_1h,
            '24h' => $this->calculateSuccessRate24h($service),
            '7d' => $this->calculateSuccessRate7d($service),
        ];
    }
}
```

2. **Response Times**
```php
class ResponseMetrics
{
    public function getResponseTimes(string $service): array
    {
        return [
            'avg_1h' => $this->calculateAverageResponseTime('1h'),
            'max_1h' => $this->calculateMaxResponseTime('1h'),
            'min_1h' => $this->calculateMinResponseTime('1h'),
            'p95_1h' => $this->calculatePercentileResponseTime(95, '1h'),
            'p99_1h' => $this->calculatePercentileResponseTime(99, '1h'),
        ];
    }
}
```

3. **Message Flow Metrics**
```php
class MessageMetrics
{
    public function getMessageMetrics(string $service): array
    {
        return [
            'total_messages' => $this->total_count_1h,
            'success_messages' => $this->success_count_1h,
            'failed_messages' => $this->total_count_1h - $this->success_count_1h,
            'messages_per_minute' => $this->calculateMessagesPerMinute(),
            'last_message_at' => $this->getLastMessageTimestamp(),
        ];
    }
}
```

4. **Availability Metrics**
```php
class AvailabilityMetrics
{
    public function getAvailabilityMetrics(string $service): array
    {
        return [
            'uptime_percentage' => $this->calculateUptimePercentage(),
            'total_downtime' => $this->calculateTotalDowntime(),
            'last_downtime' => $this->getLastDowntimeDuration(),
            'mtbf' => $this->calculateMeanTimeBetweenFailures(),
            'mttr' => $this->calculateMeanTimeToRecover(),
        ];
    }
}
```

5. **Service Health Score**
```php
class HealthMetrics
{
    public function calculateHealthScore(string $service): float
    {
        // Weighted score based on multiple factors
        return [
            'success_rate_weight' => 0.4,
            'response_time_weight' => 0.2,
            'availability_weight' => 0.3,
            'message_flow_weight' => 0.1,
        ];
    }
}
```

### Metric Collection Points

1. **Real-time Updates**:
- On message receipt
- On status change
- On error detection

2. **Scheduled Updates**:
- Hourly aggregations
- Daily rollups
- Weekly summaries

3. **On-demand Calculations**:
- API endpoints for current metrics
- Dashboard refreshes
- Alert evaluations

### Usage Examples

1. **Dashboard Display**:
```php
public function getDashboardMetrics(string $service): array
{
    return [
        'status' => $this->getCurrentStatus($service),
        'success_rates' => $this->getSuccessRates($service),
        'response_times' => $this->getResponseTimes($service),
        'message_stats' => $this->getMessageMetrics($service),
        'availability' => $this->getAvailabilityMetrics($service),
        'health_score' => $this->calculateHealthScore($service),
    ];
}
```

2. **Alert Triggers**:
```php
public function evaluateAlertConditions(string $service): void
{
    $metrics = $this->getDashboardMetrics($service);
    
    // Check various conditions
    if ($metrics['success_rates']['1h'] < 90) {
        $this->triggerAlert('success_rate_low', $metrics);
    }
    
    if ($metrics['response_times']['p95_1h'] > 1000) {
        $this->triggerAlert('response_time_high', $metrics);
    }
    
    if ($metrics['availability']['uptime_percentage'] < 99.9) {
        $this->triggerAlert('availability_low', $metrics);
    }
}
```

3. **Health Reporting**:
```php
public function generateHealthReport(string $service): array
{
    $metrics = $this->getDashboardMetrics($service);
    
    return [
        'summary' => $this->generateSummary($metrics),
        'trends' => $this->calculateTrends($metrics),
        'recommendations' => $this->generateRecommendations($metrics),
        'historical_comparison' => $this->compareWithHistorical($metrics),
    ];
}
```

## Service Status Determination

### Test Flows
The system uses a series of test flows to determine the status of each service:

1. **MQTT-only Flows (1-7)**
   - Used when only MQTT device is present
   - Tests MQTT, ChirpStack, and LoRa communication

2. **Full Flows (1-9)**
   - Used when both MQTT and HTTP devices are present
   - Tests all services including ThingsBoard integration

### Flow Analysis
The ServiceFailureAnalyzer examines patterns in failed flows to precisely identify which service is failing:

1. **Flow Patterns**
   - Each service failure creates a specific pattern of failed flows
   - Example: If ChirpStack is down, all flows requiring ChirpStack communication will fail
   - The pattern of failed/successful flows pinpoints the exact failing service

2. **Service Status Calculation**
   - System analyzes recent TestResults
   - Uses ServiceFailureAnalyzer to identify failed services from flow patterns
   - Calculates success rates and determines status per service:
     - HEALTHY: Service operating normally
     - WARNING: Service showing degraded performance
     - CRITICAL: Service confirmed down through flow analysis

### Status Types
```php
enum StatusType: string
{
    case HEALTHY = 'HEALTHY';   // All flows for this service successful
    case WARNING = 'WARNING';   // Some flows failing but service partially working
    case CRITICAL = 'CRITICAL'; // Service confirmed down through flow analysis
}
```

### Service Types
```php
enum ServiceType: string
{
    case CHIRPSTACK = 'chirpstack';
    case THINGSBOARD = 'thingsboard';
    case MQTT_TB = 'mqtt_tb';
    case MQTT_CS = 'mqtt_cs';
    case LORATX = 'loratx';
    case LORARX = 'lorarx';
}
```

### Flow Numbers and Service Mapping
Each flow tests specific services:

1. **Flow 1-3: Basic MQTT Communication**
   - Tests MQTT brokers and basic connectivity
   - Failures indicate MQTT service issues

2. **Flow 4-5: ChirpStack Integration**
   - Tests ChirpStack communication
   - Failures specific to ChirpStack service

3. **Flow 6-7: LoRa Communication**
   - Tests LoRa transmission and reception
   - Failures indicate LoRa network issues

4. **Flow 8-9: ThingsBoard Integration**
   - Only for devices with HTTP capability
   - Tests ThingsBoard connectivity and API
   - Failures specific to ThingsBoard service

### Service Status Updates
1. Test flows are executed periodically
2. Results are analyzed using ServiceFailureAnalyzer
3. Failed flows are mapped to specific services
4. Service status is updated based on:
   - Pattern of failed flows
   - Success rate of recent tests
   - Configured thresholds from monitoring config


## Flow Requirements
- Flows 1-7 are always required
- Flows 8-9 are required only when TestScenario has http_device_id configured
- All configured flows must complete successfully for the test to pass

## Flow Process

1. **Test Initialization**
   - Create TestResult in PENDING status
   - Create MessageFlows based on TestScenario configuration
   - Start with Flow 1 if initiated by ThingsBoard

2. **Flow Execution**
   - Each flow updates its status upon completion
   - Success: marks flow as 'completed'
   - Failure: marks flow as 'failed' and fails the entire test
   - Webhooks/MQTT listeners handle async flow updates

3. **Test Completion**
   - Test succeeds when all required flows complete
   - Test fails if any required flow fails
   - Execution time and status are updated

## Error Handling
- Each flow can record specific error messages
- Failed flows immediately fail the entire test
- Detailed error tracking for debugging
- Async operations handle timeouts

## Monitoring
- Track flow completion times
- Monitor success/failure rates
- Alert on flow failures
- Dashboard for flow visualization

## Best Practices
1. Always check TestScenario configuration before flow creation
2. Handle timeouts for async flows
3. Maintain detailed error messages
4. Monitor flow completion times
5. Regular cleanup of completed/failed flows

## Test Execution Flow

### Test Scenario Execution
1. **Initialization**
   - Create device_monitoring_result entry as master record
   - Status set to 'pending'
   - Links to the test scenario and device

2. **Flow Execution**
   - Start all flows simultaneously (7 or 9 depending on device type)
   - Each flow creates a MessageFlow entry linked to device_monitoring_result
   - Expected response time: 1 minute per flow

3. **Response Handling**
   - **Within 1 minute**:
     - Endpoint receives response
     - Flow marked as successful
     - Response time recorded
   
   - **After 1 minute**:
     - Flow initially marked as failed (timeout)
     - If response arrives later:
       - Flow updated to successful
       - Original response timestamp preserved
       - Important for metrics calculation
       - Helps identify slow but functioning services

4. **Test Completion**
   - Test scenario completes when:
     - All flows (7 or 9) have responses OR
     - All timeout periods (1 minute) have elapsed
   - Final status determined by analyzing flow patterns

### Flow Response Metrics
1. **Success Metrics**
   - Count of successful flows (including late responses)
   - Success rate calculation includes late successes
   - Response time distribution

2. **Performance Metrics**
   - Average response time per service
   - Percentage of responses within SLA
   - Identification of slow services

3. **Service Health Indicators**
   - Pattern of failed flows indicates specific service issues
   - Late responses may indicate service degradation
   - Timeout patterns help identify systemic issues

### Example Flow Sequence
```
Time    Event
0:00    Test scenario starts, 7 flows initiated
0:30    Flow 1,2,3 complete successfully
0:45    Flow 4,5 complete successfully
1:00    Flow 6,7 timeout (marked as failed)
1:20    Flow 6 receives late response (updated to success)
1:30    Test scenario complete
        - 6 successful flows (including 1 late)
        - 1 failed flow
        - Pattern analysis determines service status
```

## Components to Remove

1. **Test Execution Components**:
   - `ExecuteTestScenarioJob`
   - `TestExecutionService`
   - All test flow execution methods (flows 1-9)
   - `RunTestScenariosCommand`

2. **Redundant Monitoring**:
   - `MonitorDevicesCommand`
   - `DeviceMonitoringJob`
   - Separate HTTP/MQTT health checks

3. **Complex Test Scenarios**:
   - Remove scenario-based testing
   - Replace with real-time message monitoring
   - Simplify to direct device communication monitoring

4. **Status Calculation**:
   - Remove complex status calculations
   - Use real-time message success/failure
   - Simplify service status determination

## Benefits of Removal

1. **Simplified Flow**:
   - Single entry point per service
   - Clear message path
   - Real-time processing

2. **Reduced Complexity**:
   - No scheduled tests
   - No complex scenarios
   - Direct status determination

3. **Better Reliability**:
   - Real message monitoring
   - Immediate failure detection
   - Faster notification

4. **Easier Maintenance**:
   - Less code to maintain
   - Clearer responsibility
   - Simpler debugging

## Implementation Details

### Data Flow
```
HTTP Request → MessageFlowService (starts flow)
                ↓
              DeviceMessageService (send message)

HTTP Endpoints →                 
              ServiceStatusService (updates status)
                ↓ (if status changes)
              NotificationService
```

### Error Handling
- Each service implements specific error boundaries
- Errors are logged and tracked
- Failed operations are reported
- Retry mechanisms where appropriate

### Transaction Management
- Ensure data consistency across services
- Use database transactions where needed
- Handle partial failures gracefully

### Performance Considerations
- Monitor service response times
- Implement caching where appropriate
- Optimize database queries
- Use async operations when possible

### Logging
- Comprehensive logging at each step
- Track message flow through services
- Monitor error rates and types
- Performance metrics collection

## Next Steps
1. Implement HTTP endpoints
2. Create core services
3. Set up error handling
4. Implement logging
5. Add monitoring
6. Write tests

## Benefits
- Clear, single responsibility services
- Improved maintainability
- Better testability
- Simplified debugging
- Easier scaling
