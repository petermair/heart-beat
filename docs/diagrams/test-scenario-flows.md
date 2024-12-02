# Test Scenario Flows

## Downlink Flow (ThingsBoard to ChirpStack)
Status: 🟡 Warning - High latency in LoRa TX

[ThingsBoard]🟢 --0.8s--> [MQTT Broker]🟢 --1.8s--> [LoRa TX]🟡 --0.9s--> [MQTT Broker]🟢 --0.5s--> [ChirpStack]🟢

## Uplink Flow (ChirpStack to ThingsBoard)
Status: 🟢 Healthy - All services responding normally

[ChirpStack]🟢 --0.8s--> [MQTT Broker]🟢 --0.7s--> [LoRa RX]🟢 --0.6s--> [MQTT Broker]🟢 --0.5s--> [ThingsBoard]🟢

## Main Services Status
1. ThingsBoard 🟢
   - Role: Application server
   - Status: Healthy (0.8s response)
   - Throughput: 532 msg/min

2. ChirpStack 🟢
   - Role: LoRaWAN network server
   - Status: Healthy (0.5s response)
   - Throughput: 498 msg/min

3. MQTT Broker 🟢
   - Role: Message router
   - Status: Healthy (0.3s response)
   - Throughput: 1030 msg/min

4. LoRa TX 🟡
   - Role: Downlink transmission
   - Status: Warning (1.8s response)
   - Throughput: 231 msg/min

5. LoRa RX 🟢
   - Role: Uplink reception
   - Status: Healthy (0.7s response)
   - Throughput: 267 msg/min

## Status Legend
- 🟢 Healthy: Response time < 1s
- 🟡 Warning: Response time > 1s
- 🔴 Error: Service offline/failing

Note: All service names are clickable and will show detailed information.
