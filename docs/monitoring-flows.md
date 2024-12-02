# Service Monitoring Flows

## System Overview

```
Flow 1 (ThingsBoard → ChirpStack):
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ThingsBoard│───►│MQTT TB   │───►│  LoraTX  │───►│MQTT CS   │───►│ChirpStack│
│    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │
└──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘


Flow 2 (ChirpStack → ThingsBoard):
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ChirpStack│───►│MQTT CS   │───►│  LoraRX  │───►│MQTT TB   │───►│ThingsBoard│
│    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │
└──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘
```

## Test Flows

### Main Flows
1. **Full Route 1**: ThingsBoard → MQTT TB → LoraTX → MQTT CS → ChirpStack
   - Validates ThingsBoard MQTT publishing
   - Tests LoraTX message processing
   - Verifies ChirpStack message reception

2. **One-way Route**: ChirpStack → MQTT CS → LoraRX → MQTT TB → ThingsBoard
   - Validates ChirpStack MQTT publishing
   - Tests LoraRX message processing
   - Verifies ThingsBoard message reception

3. **Two-way Route**: Combines Flow 1 and Flow 2 in sequence
   - Tests complete message round-trip
   - Validates all system components

### Direct Test Flows
4. **Direct Test 1**: ChirpStack → MQTT CS → MQTT TB → ThingsBoard
   - Tests direct MQTT message routing
   - Bypasses LoraRX processing

5. **Direct Test 2**: ThingsBoard → MQTT TB → MQTT CS → ChirpStack
   - Tests direct MQTT message routing
   - Bypasses LoraTX processing

### Health Check Flows
6. **TB MQTT Health**: Device ←→ ThingsBoard (MQTT TB)
   - Direct MQTT TB connection test
   - No message transformation

7. **CS MQTT Health**: Device ←→ ChirpStack (MQTT CS)
   - Direct MQTT CS connection test
   - No message transformation

8. **TB HTTP Health**: Device ←→ ThingsBoard (HTTP)
   - Direct HTTP connection test
   - Independent of MQTT

9. **CS HTTP Health**: Device ←→ ChirpStack (HTTP)
   - Direct HTTP connection test
   - Independent of MQTT

## Service Dependencies

### ThingsBoard
- Requires MQTT TB for flows 1, 3, 4, 5, 6
- Uses HTTP for flow 8
- Independent of MQTT CS

### ChirpStack
- Requires MQTT CS for flows 1, 2, 3, 4, 5, 7
- Uses HTTP for flow 9
- Independent of MQTT TB

### MQTT TB Broker
- Required for ThingsBoard communication
- Used in flows 1, 2, 3, 4, 5, 6
- Handles JSON format messages

### MQTT CS Broker
- Required for ChirpStack communication
- Used in flows 1, 2, 3, 4, 5, 7
- Handles LPP format messages

### LoraTX Service
- Requires both MQTT TB (input) and MQTT CS (output)
- Used in flows 1, 3
- Transforms JSON to LPP format

### LoraRX Service
- Requires both MQTT CS (input) and MQTT TB (output)
- Used in flows 2, 3
- Transforms LPP to JSON format

## Alert Conditions

### Critical Alert (🔴)
- Trigger: Any of these conditions:
  - ThingsBoard/ChirpStack down for 10+ minutes
  - MQTT TB/MQTT CS broker down for 10+ minutes
  - LoraTX/LoraRX service down for 10+ minutes
- Action: Immediate notification

### Warning Alert (🟡)
- Trigger: Any of these conditions:
  - MQTT message delivery success rate < 90% over 60 minutes
  - LoraTX/LoraRX processing success rate < 90% over 60 minutes
  - ThingsBoard/ChirpStack API success rate < 90% over 60 minutes

### Healthy Status (🟢)
- All services operational
- Success rates > 90% over 60 minutes
- Message delivery within expected latency

## Service Status Tracking

Each component tracks:
- Connection status (🟢/🟡/🔴)
- Message success rate
- Processing latency
- Last successful operation timestamp

## Known Limitations

1. LoraTX requires both MQTT brokers:
   - Input: MQTT TB (JSON format)
   - Output: MQTT CS (LPP format)

2. LoraRX requires both MQTT brokers:
   - Input: MQTT CS (LPP format)
   - Output: MQTT TB (JSON format)

3. Message Tracking Challenges:
   - Complex to trace messages across brokers
   - Difficult to pinpoint failure points
   - Latency measurement spans multiple services

4. Monitoring Gaps:
   - No direct health check for message transformation
   - Limited visibility into broker message queues
   - Indirect validation of message format conversion

## Future Improvements

1. Enhanced Monitoring:
   - Add direct health checks for LoraTX/LoraRX
   - Implement message tracing across brokers
   - Add format validation checks

2. Reliability Improvements:
   - Add message persistence
   - Implement retry mechanisms
   - Add circuit breakers

3. Observability:
   - Add detailed message tracking
   - Implement distributed tracing
   - Enhanced error reporting
