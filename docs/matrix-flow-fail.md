# Service Failure Matrix

This matrix shows which flows are affected when specific services fail.

Flows:
1. TB_TO_CS: Route from ThingsBoard to ChirpStack
2. CS_TO_TB: Route from ChirpStack to ThingsBoard
3. CS_TO_TB_TO_CS: Complete round trip (TB → CS → TB)
4. DIRECT_TEST_CS_TB: Direct test ChirpStack to ThingsBoard
5. DIRECT_TEST_TB_CS: Direct test ThingsBoard to ChirpStack
6. TB_MQTT_HEALTH: ThingsBoard MQTT connection health
7. CS_MQTT_HEALTH: ChirpStack MQTT connection health
8. TB_HTTP_HEALTH: ThingsBoard HTTP connection health
9. CS_HTTP_HEALTH: ChirpStack HTTP connection health

| Service      | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 |
|--------------|---|---|---|---|---|---|---|---|---|
| ThingsBoard  | x | x | x | x | x | x | - | x | - |
| MQTT TB      | x | x | x | - | x | x | - | - | - |
| LoraTX       | x | - | x | - | - | - | - | - | - |
| LoraRX       | - | x | x | - | - | - | - | - | - |
| MQTT CS      | x | x | x | x | - | - | x | - | - |
| ChirpStack   | x | x | x | x | x | - | x | - | x |

Without Http:
-------------
| Service      | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 
|--------------|---|---|---|---|---|---|---|
| ThingsBoard  | x | x | x | x | x | x | - |
| MQTT TB      | x | x | x | - | x | x | - |
| LoraTX       | x | - | x | - | - | - | - |
| LoraRX       | - | x | x | - | - | - | - |
| MQTT CS      | x | x | x | x | - | - | x |
| ChirpStack   | x | x | x | x | x | - | x |
