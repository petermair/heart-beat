# Test Scenario Service Details

## Downlink Flows (ThingsBoard → ChirpStack)
Status: Warning - High latency detected
[ThingsBoard]🟢 --0.8s--> [MQTT Broker]🟢 --1.8s--> [LoRa TX]🟡 --0.9s--> [MQTT Broker]🟢 --0.5s--> [ChirpStack]🟢

Style:
- Status colors:
  - 🟢 Green (#00ff00): Response time < 1s
  - 🟡 Orange (#ffa500): Response time > 1s
  - 🔴 Red (#ff0000): Error or offline
- Response times: Animated counter, color matches status
- Arrows: Throughput indicator animation with status color

## Uplink Flows (ChirpStack → ThingsBoard)
Status: Healthy - All services responding normally
[ChirpStack]🟢 --0.8s--> [MQTT Broker]🟢 --0.7s--> [LoRa RX]🟢 --0.6s--> [MQTT Broker]🟢 --0.5s--> [ThingsBoard]🟢

Style:
- Status colors: Same as above
- Response time: Animated counter in green
- Arrow: Throughput indicator animation in green

## Monitored Services
1. ThingsBoard: Application server for device management and data visualization
2. ChirpStack: LoRaWAN network server
3. MQTT Broker: Message routing and communication
4. LoRa TX: Transmission of downlink messages
5. LoRa RX: Reception of uplink messages

## Status Color Legend
- 🟢 Green (#00ff00): Healthy service, response time < 1s
- 🟡 Orange (#ffa500): Warning, response time > 1s
- 🔴 Red (#ff0000): Error, service offline or failing

## Animation Requirements
1. **Service Performance**:
   - Response times: Color-coded animated counters
   - Success rates: Circular progress with status colors
   - Load indicators: Pulsing effect in status color

2. **Flow Type Headers**:
   - Success rate color matches overall status
   - Background intensity reflects health
   - Warning/error icons with attention animation

3. **Service Connections**:
   - Throughput visualization inherits status color
   - Response time updates with color transition
   - Failed connections shown in red with alert animation

4. **Metrics Display**:
   - Real-time updates with color transitions
   - Hover tooltips with detailed stats
   - Click to expand historical data with color coding
