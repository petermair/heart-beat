# Message Flow Integration Plan

## Overview
This document outlines the implementation steps for the message flow system between ThingsBoard and ChirpStack.

## Components Overview

### Models
1. `MonitoringResult`
   - Tracks overall test execution
   - Status: PENDING, SUCCESS, FAILURE, TIMEOUT
   - Relations: hasMany MessageFlows

2. `MessageFlow`
   - Tracks individual message flows
   - Fields:
     - monitoring_result_id (foreign key)
     - flow_type (enum)
     - status (pending, completed, failed)

### Enums
1. `FlowType`
   ```php
   enum FlowType: string
   {
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
   }
   ```

2. `TestResultStatus`
   ```php
   enum TestResultStatus: string
   {
       case PENDING = 'PENDING';   // Initial state when test starts
       case SUCCESS = 'SUCCESS';   // Test completed successfully
       case FAILURE = 'FAILURE';   // Test failed with an error
       case TIMEOUT = 'TIMEOUT';   // Test didn't complete within expected time
   }
   ```

3. `StatusType`
   ```php
   enum StatusType: string
   {
       case HEALTHY = 'HEALTHY';   // Service is up and running normally
       case WARNING = 'WARNING';   // Service has issues but operational
       case CRITICAL = 'CRITICAL'; // Service is down or not responding
   }
   ```

4. `MessageFlowStatusEnum`
   ```php
   enum MessageFlowStatusEnum: string
   {
       case PENDING = 'pending';
       case COMPLETED = 'completed';
       case FAILED = 'failed';
   }
   ```

### Services
1. `TestExecutionService`
   - Initializes test execution
   - Creates required message flows
   - Updates test status

2. `DeviceCommunicationService`
   - Handles MQTT communication
   - Formats messages (JSON/LPP)
   - Sends to ChirpStack/ThingsBoard

3. `MessageFlowService`
   - Manages message flow lifecycle
   - Updates flow status
   - Validates flow completion

4. `WebhookService`
   - Processes incoming webhooks
   - Validates message formats
   - Updates flow status

## Implementation Steps

### Step 1: Flow Types and Status Enums

### Step 2: Database Schema

#### Message Flows Table
```sql
CREATE TABLE message_flows (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    monitoring_result_id bigint unsigned NOT NULL,
    flow_type varchar(255) NOT NULL,
    status enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (id),
    KEY message_flows_monitoring_result_id_foreign (monitoring_result_id),
    CONSTRAINT message_flows_monitoring_result_id_foreign 
        FOREIGN KEY (monitoring_result_id) 
        REFERENCES monitoring_results (id) 
        ON DELETE CASCADE
);
```

### Step 3: Service Implementation

#### 3.1 Initialize Test
```php
class TestExecutionService
{
    public function initializeTest(Device $device, string $testType): MonitoringResult
    {
        $result = MonitoringResult::create([
            'status' => TestResultStatus::PENDING
        ]);
        
        // Create flows based on test type
        $flows = match($testType) {
            'route' => [
                FlowType::TB_TO_CS,
                FlowType::CS_TO_TB,
                FlowType::TB_CS_TB
            ],
            'direct' => [
                FlowType::DIRECT_TEST_CS_TB,
                FlowType::DIRECT_TEST_TB_CS
            ],
            'health' => [
                FlowType::TB_MQTT_HEALTH,
                FlowType::CS_MQTT_HEALTH,
                FlowType::TB_HTTP_HEALTH,
                FlowType::CS_HTTP_HEALTH
            ]
        };

        foreach ($flows as $flowType) {
            MessageFlow::create([
                'monitoring_result_id' => $result->id,
                'flow_type' => $flowType,
                'status' => MessageFlowStatusEnum::PENDING
            ]);
        }
        
        return $result;
    }

    public function handleTimeout(MonitoringResult $result)
    {
        $result->update(['status' => TestResultStatus::TIMEOUT]);
        
        // Update any pending flows to failed
        MessageFlow::where('monitoring_result_id', $result->id)
            ->where('status', MessageFlowStatusEnum::PENDING)
            ->update(['status' => MessageFlowStatusEnum::FAILED]);
    }
}
```

#### 3.2 Send Messages
```php
class DeviceCommunicationService
{
    public function sendMessage(MonitoringResult $result, FlowType $flowType)
    {
        try {
            match($flowType) {
                // Route messages
                FlowType::TB_TO_CS, 
                FlowType::DIRECT_TEST_TB_CS => $this->sendToChirpStack($result, $flowType),
                
                FlowType::CS_TO_TB,
                FlowType::DIRECT_TEST_CS_TB => $this->sendToThingsBoard($result, $flowType),
                
                FlowType::TB_CS_TB => $this->handleRoundTrip($result),
                
                // Health checks
                FlowType::TB_MQTT_HEALTH,
                FlowType::CS_MQTT_HEALTH => $this->checkMqttHealth($flowType),
                
                FlowType::TB_HTTP_HEALTH,
                FlowType::CS_HTTP_HEALTH => $this->checkHttpHealth($flowType)
            };
        } catch (Exception $e) {
            $this->handleError($result, $flowType, $e);
        }
    }

    private function handleError(MonitoringResult $result, FlowType $flowType, Exception $e)
    {
        // Update flow status
        MessageFlow::where('monitoring_result_id', $result->id)
            ->where('flow_type', $flowType)
            ->update(['status' => MessageFlowStatusEnum::FAILED]);

        // Update test result if needed
        $result->update(['status' => TestResultStatus::FAILURE]);

        // Update service status based on error
        $this->updateServiceStatus($flowType, StatusType::WARNING);
        
        if ($this->isServiceDown($flowType)) {
            $this->updateServiceStatus($flowType, StatusType::CRITICAL);
        }
    }
}
```

#### 3.3 Webhook Processing
```php
class WebhookService
{
    public function processWebhook(string $source, Request $request)
    {
        $data = $source === 'chirpstack' 
            ? $this->decodeLppData($request->input('data'))
            : $request->json();

        $flowType = FlowType::from($data['f001digitalinput1']);
        
        $this->messageFlowService->updateFlow(
            monitoringResultId: $data['f001unsigned4b2'],
            flowType: $flowType
        );
    }
}
```

#### 3.4 Flow Status Update
```php
class MessageFlowService
{
    public function updateFlow(int $monitoringResultId, FlowType $flowType)
    {
        $flow = MessageFlow::where('monitoring_result_id', $monitoringResultId)
            ->where('flow_type', $flowType)
            ->first();
            
        $flow->update(['status' => MessageFlowStatusEnum::COMPLETED]);

        $this->checkTestCompletion($monitoringResultId);
    }

    private function checkTestCompletion(int $monitoringResultId)
    {
        $result = MonitoringResult::with('flows')->find($monitoringResultId);
        
        // Check if all flows completed
        $allCompleted = $result->flows->every(fn($flow) => 
            $flow->status === MessageFlowStatusEnum::COMPLETED
        );
        
        if ($allCompleted) {
            $result->update(['status' => TestResultStatus::SUCCESS]);
            
            // Update service status based on test type
            foreach ($result->flows as $flow) {
                if ($this->isHealthCheck($flow->flow_type)) {
                    $this->updateServiceStatus(
                        $flow->flow_type, 
                        StatusType::HEALTHY
                    );
                }
            }
        }
    }

    private function isHealthCheck(FlowType $flowType): bool
    {
        return in_array($flowType, [
            FlowType::TB_MQTT_HEALTH,
            FlowType::CS_MQTT_HEALTH,
            FlowType::TB_HTTP_HEALTH,
            FlowType::CS_HTTP_HEALTH
        ]);
    }
}
```

## Testing Plan

1. Unit Tests
   - Test each service method in isolation
   - Mock MQTT and HTTP clients
   - Verify flow status updates

2. Integration Tests
   - Test complete message flow
   - Verify webhook processing
   - Check test completion logic

3. End-to-End Tests
   - Test with real devices
   - Verify message formats
   - Check timing and metrics
