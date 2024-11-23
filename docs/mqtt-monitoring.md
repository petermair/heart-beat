# IoT Platform Monitoring via MQTT & HTTP

## System Übersicht

### Instanz-Typen
1. **ThingsBoard Instanzen**
   - Production Instance(s)
   - Staging Instance(s)
   - Development Instance(s)
   - Jede Instanz mit eigenem:
     - Host/Port
     - MQTT Broker
     - Zugangsdaten
     - Device Set

2. **ChirpStack Instanzen**
   - Production Instance(s)
   - Staging Instance(s)
   - Development Instance(s)
   - Jede Instanz mit eigenem:
     - Host/Port
     - MQTT Broker
     - API Key
     - Application Set
     - Device Set

3. **Service Verbindungen**
   ```mermaid
   graph TD
      TB1[ThingsBoard Prod] --> LTX1[LoraTX Prod] --> CS1[ChirpStack Prod]
      CS1 --> LRX1[LoraRX Prod] --> TB1
      
      TB2[ThingsBoard Staging] --> LTX2[LoraTX Staging] --> CS2[ChirpStack Staging]
      CS2 --> LRX2[LoraRX Staging] --> TB2
   ```

### Komponenten pro Instanz
- ThingsBoard Server
- ChirpStack Server
- MQTT Broker (Mosquitto)
- LoRaRX Service
- LoraTX Service
- Monitoring Devices:
  - MQTT Heart-Beat Device
  - HTTP Test Device
  - Routing Test Device
  - Direct Test Device

## Monitoring Konfiguration

### Instance Groups
```json
{
  "production": {
    "thingsboard": {
      "primary": {
        "host": "tb-prod-1.example.com",
        "mqtt_port": 1883,
        "http_port": 443,
        "ssl": true,
        "devices": {
          "mqtt_heartbeat": {
            "access_token": "token1",
            "device_id": "me"
          },
          "http_test": {
            "access_token": "token2",
            "device_id": "me"
          }
        }
      },
      "secondary": {
        "host": "tb-prod-2.example.com",
        // ... ähnliche Struktur
      }
    },
    "chirpstack": {
      "primary": {
        "host": "cs-prod-1.example.com",
        "mqtt_port": 1883,
        "http_port": 443,
        "ssl": true,
        "api_key": "key1",
        "applications": {
          "main": {
            "id": "1",
            "devices": {
              "test_device": {
                "dev_eui": "0000000000000001",
                "app_key": "key1"
              }
            }
          }
        }
      },
      "secondary": {
        "host": "cs-prod-2.example.com",
        // ... ähnliche Struktur
      }
    }
  },
  "staging": {
    // ... ähnliche Struktur wie production
  },
  "development": {
    // ... ähnliche Struktur wie production
  }
}
```

### Test Matrix
```json
{
  "test_scenarios": [
    {
      "name": "mqtt_heartbeat",
      "instances": ["production.primary", "production.secondary"],
      "interval": 30,
      "timeout": 5,
      "retries": 3
    },
    {
      "name": "http_test",
      "instances": ["production.primary", "staging.primary"],
      "interval": 60,
      "timeout": 10,
      "retries": 2
    },
    {
      "name": "routing_test",
      "instance_pairs": [
        {
          "thingsboard": "production.primary",
          "chirpstack": "production.primary"
        },
        {
          "thingsboard": "production.secondary",
          "chirpstack": "production.secondary"
        }
      ],
      "interval": 120,
      "timeout": 15,
      "retries": 3
    }
  ]
}
```

### Health Status Aggregation
1. **Per Instance**
   ```json
   {
     "instance": "production.thingsboard.primary",
     "components": {
       "server": "healthy",
       "mqtt": "healthy",
       "http": "healthy"
     },
     "overall": "healthy",
     "last_check": "2024-01-23T12:00:00Z",
     "metrics": {
       "response_times": {
         "mqtt": 120,
         "http": 200
       }
     }
   }
   ```

2. **Per Service Pair**
   ```json
   {
     "pair": {
       "thingsboard": "production.primary",
       "chirpstack": "production.primary"
     },
     "components": {
       "lorarx": "healthy",
       "loratx": "healthy",
       "routing": "healthy"
     },
     "overall": "healthy",
     "metrics": {
       "routing_time": 850,
       "direct_time": 400
     }
   }
   ```

3. **System Wide**
   ```json
   {
     "environment": "production",
     "status": {
       "overall": "degraded",
       "thingsboard": {
         "primary": "healthy",
         "secondary": "degraded"
       },
       "chirpstack": {
         "primary": "healthy",
         "secondary": "healthy"
       }
     },
     "affected_services": [
       "production.thingsboard.secondary.mqtt"
     ]
   }
   ```

## Implementation Details

### Instance Manager
```php
class InstanceManager
{
    protected array $instances = [];
    protected array $testMatrix = [];
    
    public function registerInstance(string $type, string $name, array $config): void
    {
        $this->instances[$type][$name] = $config;
    }
    
    public function getInstancePairs(): array
    {
        // Returns valid TB-CS pairs for testing
    }
    
    public function getTestScenarios(): array
    {
        // Returns configured test scenarios
    }
}
```

### Test Orchestrator
```php
class TestOrchestrator
{
    protected InstanceManager $instances;
    protected array $activeTests = [];
    
    public function scheduleTests(): void
    {
        foreach ($this->instances->getTestScenarios() as $scenario) {
            $this->scheduleTest($scenario);
        }
    }
    
    protected function scheduleTest(array $scenario): void
    {
        // Schedule test based on interval
    }
}
```

### Status Aggregator
```php
class StatusAggregator
{
    public function aggregateInstanceStatus(string $instance): array
    {
        // Aggregate status for single instance
    }
    
    public function aggregatePairStatus(array $pair): array
    {
        // Aggregate status for TB-CS pair
    }
    
    public function aggregateSystemStatus(): array
    {
        // Aggregate overall system status
    }
}
```

## System Übersicht

### Komponenten
- ThingsBoard: IoT Platform (MQTT + HTTP)
- ChirpStack: LoRaWAN Network Server (MQTT + HTTP)
- LoRaRX Service: Empfängt von ChirpStack, sendet an ThingsBoard
- LoraTX Service: Empfängt von ThingsBoard, sendet an ChirpStack
- Mosquitto: MQTT Broker

### Test Szenarien

#### 1. Direktes ThingsBoard Heart-Beat
- **Device**: ThingsBoard-Only Device
- **Ablauf**: 
  - Device sendet Heart-Beat an ThingsBoard
  - ThingsBoard antwortet direkt
- **Erfolg wenn**: Antwort innerhalb Timeout empfangen

#### 2. ThingsBoard-ChirpStack Routing (via Services)
- **Device**: Simulated LoRa Device
- **Ablauf**:
  1. Device -> ChirpStack (Uplink)
  2. ChirpStack -> LoRaRX -> ThingsBoard
  3. ThingsBoard -> LoraTX -> ChirpStack (Downlink)
  4. ChirpStack -> Device (ACK)
- **Erfolg wenn**: Kompletter Zyklus durchlaufen

#### 3. ThingsBoard-ChirpStack Direkt (Service Test)
- **Device**: Direct Communication Device
- **Ablauf**:
  1. Device sendet direkt an ThingsBoard
  2. ThingsBoard sendet direkt an ChirpStack
  3. Vergleich mit Routing-Weg
- **Erfolg wenn**: Unterschiede in Routing zeigen Service-Probleme

#### 4. ThingsBoard MQTT vs HTTP Test
- **Devices**: 
  - MQTT Device (Device-ID "me")
  - HTTP Device (gleiches Device, andere Transportschicht)
- **Ablauf MQTT**: 
  - Device sendet Heart-Beat via MQTT
  - ThingsBoard antwortet via MQTT
- **Ablauf HTTP**:
  ```http
  POST /api/v1/{access-token}/telemetry
  Host: {thingsboard-host}
  Content-Type: application/json
  
  {
    "heartbeat": {
      "requestId": "uuid-v4",
      "timestamp": "iso-8601-timestamp",
      "type": "http-test"
    }
  }
  ```
- **Erfolg wenn**: 
  - Beide Wege funktionieren: Mosquitto & ThingsBoard gesund
  - Nur HTTP funktioniert: Mosquitto Problem
  - Keiner funktioniert: ThingsBoard Problem

#### 5. ChirpStack MQTT vs HTTP Test
- **Devices**:
  - MQTT Device (via Application Server)
  - HTTP Device (via REST API)
- **Ablauf MQTT**: Wie bisher
- **Ablauf HTTP**:
  ```http
  POST /api/devices/{dev-eui}/queue
  Host: {chirpstack-host}
  Authorization: Bearer {api-token}
  Content-Type: application/json
  
  {
    "deviceQueueItem": {
      "data": "base64-encoded-payload",
      "fPort": 11,
      "reference": "uuid-v4"
    }
  }
  ```
- **Erfolg wenn**:
  - Beide Wege funktionieren: Komplettes System gesund
  - Nur HTTP funktioniert: MQTT Probleme
  - Keiner funktioniert: ChirpStack Problem

## Implementierungsdetails

### 1. ThingsBoard Heart-Beat Device
```json
// Request (Device -> ThingsBoard)
Topic: v1/devices/me/telemetry
Payload: {
  "heartbeat": {
    "requestId": "uuid-v4",
    "timestamp": "iso-8601-timestamp",
    "type": "direct"
  }
}

// Response (ThingsBoard -> Device)
Topic: v1/devices/me/rpc/request/{requestId}
Payload: {
  "heartbeat": {
    "responseId": "uuid-v4",
    "requestId": "original-request-id",
    "timestamp": "iso-8601-timestamp",
    "status": "ok"
  }
}
```

### 2. LoRa Routing Test Device
```json
// Uplink (Device -> ChirpStack)
Topic: application/{applicationID}/device/{devEUI}/tx
Payload: {
  "reference": "uuid-v4",
  "fPort": 11,
  "data": "base64-encoded-heartbeat",
  "type": "routing-test"
}

// ChirpStack -> LoRaRX -> ThingsBoard Flow
// ThingsBoard -> LoraTX -> ChirpStack Flow
// Final ACK (ChirpStack -> Device)
Topic: application/{applicationID}/device/{devEUI}/rx
Payload: {
  "reference": "original-reference",
  "status": "completed",
  "route": ["chirpstack", "lorarx", "thingsboard", "loratx", "chirpstack"]
}
```

### 3. Direct Communication Test Device
```json
// ThingsBoard Direct Request
Topic: v1/devices/me/telemetry
Payload: {
  "heartbeat": {
    "requestId": "uuid-v4",
    "timestamp": "iso-8601-timestamp",
    "type": "service-test"
  }
}

// ChirpStack Direct Response
Topic: application/{applicationID}/device/{devEUI}/event/up
Payload: {
  "reference": "original-request-id",
  "status": "direct-response"
}
```

## Monitoring Parameter

### Timeouts
- Heart-Beat Response: 5 Sekunden
- Routing Test Completion: 15 Sekunden
- Service Test Comparison: 10 Sekunden

### Health Status Definition
- **Healthy**: Alle Tests erfolgreich
- **Degraded**: 
  - Heart-Beat okay, aber Routing/Service-Tests fehlgeschlagen
  - Routing funktioniert, aber langsamer als Direct
- **Unhealthy**: Heart-Beat fehlgeschlagen

### Metriken
1. **Response Times**
   - Heart-Beat Response Time
   - Routing Path Complete Time
   - Direct Path Time
   - Service Processing Time (Differenz Routing vs Direct)

2. **Success Rates**
   - Heart-Beat Success Rate
   - Routing Test Success Rate
   - Service Test Success Rate

3. **Service Health**
   - LoRaRX Status (abgeleitet)
   - LoraTX Status (abgeleitet)
   - End-to-End Verfügbarkeit

4. **Transport Layer Health**
   - MQTT vs HTTP Response Times
   - MQTT Success Rate
   - HTTP Success Rate
   - Mosquitto Status
   - Transport Layer Preference

## Error Handling
- Timeout Tracking pro Teststufe
- Service Unavailable Detection
- Routing Path Analysis
- Vergleichsanalyse Direct vs Routing

## Error Scenarios
- **Mosquitto Down**
  - MQTT Tests schlagen fehl
  - HTTP Tests erfolgreich
  - System "Degraded" aber funktionsfähig

- **ThingsBoard Down**
  - Beide Transport Layer schlagen fehl
  - System "Unhealthy"

- **ChirpStack Down**
  - Beide Transport Layer schlagen fehl
  - System "Unhealthy"

- **LoRaRX/TX Services Down**
  - MQTT/HTTP Basis-Kommunikation funktioniert
  - Routing Tests schlagen fehl
  - System "Degraded"
