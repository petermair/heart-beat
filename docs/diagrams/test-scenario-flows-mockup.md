# Test Scenario Flows - UI Mockup

## Header Section
```
[Page Title: Test Scenario Flows]
[Navigation: Home > Monitoring > Test Flows]
```

## Flow Section
```
[Tab: Main Flows]

[Box: Downlink Flow]
Title: "Downlink Flow (ThingsBoard to ChirpStack)"
Status Banner: "🟡 Warning - High latency in LoRa TX"

Flow Display:
[ThingsBoard]🟢 --0.8s--> [MQTT Broker]🟢 --1.8s--> [LoRa TX]🟡 --0.9s--> [MQTT Broker]🟢 --0.5s--> [ChirpStack]🟢

[Box: Uplink Flow]
Title: "Uplink Flow (ChirpStack to ThingsBoard)"
Status Banner: "🟢 Healthy - All services responding normally"

Flow Display:
[ChirpStack]🟢 --0.8s--> [MQTT Broker]🟢 --0.7s--> [LoRa RX]🟢 --0.6s--> [MQTT Broker]🟢 --0.5s--> [ThingsBoard]🟢

[Tab: Test Flows]
Layout: Grid with 7 flow cards

[Flow 1: JSON Downlink]
Title: "JSON → LPP (Downlink)"
Status: 🟢 Healthy
[ThingsBoard] --0.8s--> [MQTT Broker] --0.5s--> [ChirpStack]

[Flow 2: LPP Uplink]
Title: "LPP → JSON (Uplink)"
Status: 🟢 Healthy
[ChirpStack] --0.7s--> [MQTT Broker] --0.6s--> [ThingsBoard]

[Flow 3: Two-Way MQTT]
Title: "Two-Way MQTT"
Status: 🟢 Healthy
[ThingsBoard] <--0.8s--> [MQTT Broker] <--0.7s--> [ChirpStack]

[Flow 4: LoRa Downlink]
Title: "LoRa Downlink"
Status: 🟡 Warning
[MQTT Broker] --1.8s--> [LoRa TX] --0.9s--> [MQTT Broker]

[Flow 5: LoRa Uplink]
Title: "LoRa Uplink"
Status: 🟢 Healthy
[MQTT Broker] --0.7s--> [LoRa RX] --0.6s--> [MQTT Broker]

[Flow 6: End-to-End Downlink]
Title: "End-to-End Downlink"
Status: 🟡 Warning
[ThingsBoard] --0.8s--> [MQTT Broker] --1.8s--> [LoRa TX] --0.9s--> [MQTT Broker] --0.5s--> [ChirpStack]

[Flow 7: End-to-End Uplink]
Title: "End-to-End Uplink"
Status: 🟢 Healthy
[ChirpStack] --0.8s--> [MQTT Broker] --0.7s--> [LoRa RX] --0.6s--> [MQTT Broker] --0.5s--> [ThingsBoard]
```

## Services Section
```
[Box: Main Services Status]
Layout: Grid with 5 clickable service cards

[Card 1: ThingsBoard]
Status Icon: 🟢
Role: Application server
Status: Healthy (0.8s response)
Throughput: 532 msg/min

[Card 2: ChirpStack]
Status Icon: 🟢
Role: LoRaWAN network server
Status: Healthy (0.5s response)
Throughput: 498 msg/min

[Card 3: MQTT Broker]
Status Icon: 🟢
Role: Message router
Status: Healthy (0.3s response)
Throughput: 1030 msg/min

[Card 4: LoRa TX]
Status Icon: 🟡
Role: Downlink transmission
Status: Warning (1.8s response)
Throughput: 231 msg/min

[Card 5: LoRa RX]
Status Icon: 🟢
Role: Uplink reception
Status: Healthy (0.7s response)
Throughput: 267 msg/min
```

## Footer Section
```
[Box: Status Legend]
Display: Horizontal list
- 🟢 Healthy: Response time < 1s
- 🟡 Warning: Response time > 1s
- 🔴 Error: Service offline/failing

[Note Box]
Text: "All service names are clickable and will show detailed information"
```

## UI Elements
- All service names should be clickable
- Status colors should be consistent throughout the UI
- Response times should be prominently displayed
- Service cards should have hover effect to indicate clickability
- Flow cards should expand on click to show detailed metrics
- Tabs should switch between main flows and test flows views
