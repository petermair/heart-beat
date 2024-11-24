# Service Monitoring Flows

## System Overview

```
Flow 1 (ThingsBoard → ChirpStack):
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ThingsBoard│───►│   MQTT   │───►│  LoraTX  │───►│   MQTT   │───►│ChirpStack│
│    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │
└──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘


Flow 2 (ChirpStack → ThingsBoard):
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ChirpStack│───►│   MQTT   │───►│  LoraRX  │───►│   MQTT   │───►│ThingsBoard│
│    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │    │    🟢    │
└──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘
```

## Test Flows

### Main Flows
1. **Full Route 1**: Device → TB → MQTT → LoraTX → MQTT → CS → Device
2. **One-way Route**: Device → CS → MQTT → LoraRX → MQTT → TB
3. **Two-way Route**: Device → CS → MQTT → LoraRX → MQTT → TB → (Flow 1 back)

### Direct Test Flows
4. **Direct Test 1**: Device → CS → MQTT → TB
5. **Direct Test 2**: Device → TB → MQTT → CS → Device

### Health Check Flows
6. **TB MQTT Health**: Device ←→ TB (MQTT)
7. **CS MQTT Health**: Device ←→ CS (MQTT)
8. **TB HTTP Health**: Device ←→ TB (HTTP)
9. **CS HTTP Health**: Device ←→ CS (HTTP)

## Service Coverage

### ThingsBoard
- MQTT Coverage: Flows 1, 3, 4, 5, 6
- HTTP Coverage: Flow 8
- Status: Well covered (both protocols)

### ChirpStack
- MQTT Coverage: Flows 1, 2, 3, 4, 5, 7
- HTTP Coverage: Flow 9
- Status: Well covered (both protocols)

### MQTT Broker
- Coverage: Flows 1, 2, 3, 4, 5, 6, 7
- Status: Well covered

### LoraTX
- Coverage: Flow 1
- Status: Limited coverage

### LoraRX
- Coverage: Flows 2, 3
- Status: Limited coverage

## Alert Conditions

### Critical Alert (🔴)
- Trigger: Service down for 10+ minutes
- Action: Immediate notification
- Example: "MQTT Broker down for 12 minutes"

### Warning Alert (🟡)
- Trigger: Service success rate < 90% over 60 minutes
- Action: Hourly notification
- Example: "LoraTX success rate at 85% for last hour"

### Healthy Status (🟢)
- Condition: Service up and running
- Success rate > 90% over 60 minutes

## Service Status Display

Each service shows:
- Status indicator (🟢/🟡/🔴)
- Success rate or downtime duration
- Last successful message timestamp

## Known Limitations

1. LoraTX service only tested in Flow 1
2. LoraRX service only tested in Flows 2 and 3
3. Cannot always distinguish between MQTT failures and LoraTX/LoraRX failures

## Future Improvements

1. Add direct health checks for LoraTX and LoraRX services
2. Implement better error detection between MQTT and service failures
3. Consider additional test paths for LoraTX and LoraRX
