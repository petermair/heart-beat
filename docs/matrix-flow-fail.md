# Service Failure Matrix

This matrix shows which flows are affected when specific services fail.

Flows:
1. Full Route 1 (TB → CS)
2. One Way Route (CS → TB)
3. Two Way Route (CS → TB → CS)
4. Direct Test 1 (CS → TB)
5. Direct Test 2 (TB → CS)
6. TB MQTT Health
7. CS MQTT Health
8. TB HTTP Health
9. CS HTTP Health

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
