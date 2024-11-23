# Datenbank Schema

## Server Types
```sql
CREATE TABLE server_types (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    interface_class varchar(255) NOT NULL,
    description text,
    required_settings json NOT NULL,
    required_credentials json NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Beispiel-Einträge
INSERT INTO server_types (name, interface_class, description, required_settings, required_credentials) VALUES
('thingsboard', 'App\\Services\\Monitoring\\ThingsBoardMonitor', 'ThingsBoard IoT Platform', 
    '["host", "port", "ssl"]',
    '["access_token"]'
),
('chirpstack', 'App\\Services\\Monitoring\\ChirpStackMonitor', 'ChirpStack LoRaWAN Network Server', 
    '["host", "port", "ssl", "application_id"]',
    '["api_key"]'
);
```

## Servers
```sql
CREATE TABLE servers (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    server_type_id bigint unsigned NOT NULL,
    name varchar(255) NOT NULL,
    settings json NOT NULL,
    credentials json NOT NULL,
    description text,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (server_type_id) REFERENCES server_types(id)
);

-- Beispiel-Eintrag für ThingsBoard
INSERT INTO servers (server_type_id, name, settings, credentials, description) VALUES
(1, 'ThingsBoard Production', 
    '{
        "host": "thingsboard.example.com",
        "port": 1883,
        "ssl": true
    }',
    '{
        "access_token": "YOUR_ACCESS_TOKEN"
    }',
    'Production ThingsBoard Instance'
);

-- Beispiel-Eintrag für ChirpStack
INSERT INTO servers (server_type_id, name, settings, credentials, description) VALUES
(2, 'ChirpStack Production',
    '{
        "host": "chirpstack.example.com",
        "port": 1883,
        "ssl": true,
        "application_id": "1"
    }',
    '{
        "api_key": "YOUR_API_KEY"
    }',
    'Production ChirpStack Instance'
);

-- ChirpStack Instance Examples
INSERT INTO servers (server_type_id, name, settings, credentials, description) VALUES
(2, 'ChirpStack Star1', 
    '{
        "host": "star1.likem13.com",
        "port": 8080,
        "ssl": true,
        "application_id": "1"
    }',
    '{
        "api_key": "STAR1_API_KEY"
    }',
    'ChirpStack Star1 Instance'
),
(2, 'ChirpStack Star2',
    '{
        "host": "star2.likem13.com",
        "port": 8080,
        "ssl": true,
        "application_id": "1"
    }',
    '{
        "api_key": "STAR2_API_KEY"
    }',
    'ChirpStack Star2 Instance'
),
(2, 'ChirpStack Star3',
    '{
        "host": "star3.likem13.com",
        "port": 8080,
        "ssl": true,
        "application_id": "1"
    }',
    '{
        "api_key": "STAR3_API_KEY"
    }',
    'ChirpStack Star3 Instance'
);

-- ThingsBoard Instances
INSERT INTO servers (server_type_id, name, settings, credentials, description) VALUES
(1, 'ThingsBoard Main', 
    '{
        "host": "things.likem13.com",
        "port": 1883,
        "ssl": true
    }',
    '{
        "access_token": "MAIN_ACCESS_TOKEN"
    }',
    'Main ThingsBoard Instance'
),
(1, 'ThingsBoard Customer1',
    '{
        "host": "customer1.things.likem13.com",
        "port": 1883,
        "ssl": true
    }',
    '{
        "access_token": "CUSTOMER1_ACCESS_TOKEN"
    }',
    'Customer 1 ThingsBoard Instance'
);
```

## Server Health Status
```sql
CREATE TABLE server_health_status (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    server_id bigint unsigned NOT NULL,
    status varchar(50) NOT NULL,
    last_check timestamp NOT NULL,
    error_message text,
    metrics json,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (server_id) REFERENCES servers(id)
);
```

## Monitoring Logs
```sql
CREATE TABLE monitoring_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    server_id bigint unsigned NOT NULL,
    event_type varchar(50) NOT NULL,
    message text NOT NULL,
    context json,
    created_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (server_id) REFERENCES servers(id)
);
```

## Monitoring Devices
```sql
CREATE TABLE monitoring_devices (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    thingsboard_server_id bigint unsigned NOT NULL,
    chirpstack_server_id bigint unsigned NOT NULL,
    device_type enum('RX', 'TX', 'HEALTH') NOT NULL,
    settings json NOT NULL,
    credentials json NOT NULL,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (thingsboard_server_id) REFERENCES servers(id),
    FOREIGN KEY (chirpstack_server_id) REFERENCES servers(id)
);

-- Example Device for RX Monitoring
INSERT INTO monitoring_devices (
    name, 
    description, 
    thingsboard_server_id, 
    chirpstack_server_id, 
    device_type,
    settings,
    credentials
) VALUES (
    'RX_Monitor_Star1_Main',
    'RX Service Monitor between Star1 and Main ThingsBoard',
    1, -- ThingsBoard Main Instance ID
    2, -- ChirpStack Star1 Instance ID
    'RX',
    '{
        "monitoring_interval": 300,
        "timeout": 30,
        "expected_messages": ["uplink", "status"],
        "thingsboard_device_name": "rx_monitor_star1",
        "chirpstack_device_name": "rx_monitor_dev1"
    }',
    '{
        "thingsboard_access_token": "TB_DEVICE_ACCESS_TOKEN_1",
        "chirpstack_device_eui": "DEVICE_EUI_1",
        "chirpstack_app_key": "APP_KEY_1"
    }'
);

-- Example Device for TX Monitoring
INSERT INTO monitoring_devices (
    name, 
    description, 
    thingsboard_server_id, 
    chirpstack_server_id, 
    device_type,
    settings,
    credentials
) VALUES (
    'TX_Monitor_Star1_Main',
    'TX Service Monitor between Star1 and Main ThingsBoard',
    1, -- ThingsBoard Main Instance ID
    2, -- ChirpStack Star1 Instance ID
    'TX',
    '{
        "monitoring_interval": 300,
        "timeout": 30,
        "expected_messages": ["downlink", "ack"],
        "thingsboard_device_name": "tx_monitor_star1",
        "chirpstack_device_name": "tx_monitor_dev1"
    }',
    '{
        "thingsboard_access_token": "TB_DEVICE_ACCESS_TOKEN_2",
        "chirpstack_device_eui": "DEVICE_EUI_2",
        "chirpstack_app_key": "APP_KEY_2"
    }'
);

-- Example Device for Health Monitoring
INSERT INTO monitoring_devices (
    name, 
    description, 
    thingsboard_server_id, 
    chirpstack_server_id, 
    device_type,
    settings,
    credentials
) VALUES (
    'Health_Monitor_Star1_Main',
    'Health Monitor between Star1 and Main ThingsBoard',
    1, -- ThingsBoard Main Instance ID
    2, -- ChirpStack Star1 Instance ID
    'HEALTH',
    '{
        "monitoring_interval": 60,
        "timeout": 15,
        "check_endpoints": ["mqtt", "http"],
        "thingsboard_device_name": "health_monitor_star1",
        "chirpstack_device_name": "health_monitor_dev1"
    }',
    '{
        "thingsboard_access_token": "TB_DEVICE_ACCESS_TOKEN_3",
        "chirpstack_device_eui": "DEVICE_EUI_3",
        "chirpstack_app_key": "APP_KEY_3"
    }'
);
```

## Device Status
```sql
CREATE TABLE device_status (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    monitoring_device_id bigint unsigned NOT NULL,
    status varchar(50) NOT NULL,
    last_check timestamp NOT NULL,
    message_type varchar(50),
    latency int unsigned,
    error_message text,
    metrics json,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (monitoring_device_id) REFERENCES monitoring_devices(id)
);
```

## Device Monitoring Logs
```sql
CREATE TABLE device_monitoring_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    monitoring_device_id bigint unsigned NOT NULL,
    event_type varchar(50) NOT NULL,
    direction enum('TX', 'RX') NOT NULL,
    message text NOT NULL,
    context json,
    created_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (monitoring_device_id) REFERENCES monitoring_devices(id)
);
```

## Message Payloads and Routes
```sql
CREATE TABLE message_payloads (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    monitoring_device_id bigint unsigned NOT NULL,
    message_id varchar(255) NOT NULL,
    payload_type enum('HEARTBEAT', 'STATUS', 'COMMAND', 'RESPONSE') NOT NULL,
    direction enum('UPLINK', 'DOWNLINK') NOT NULL,
    payload json NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (monitoring_device_id) REFERENCES monitoring_devices(id),
    INDEX idx_message_id (message_id),
    INDEX idx_monitoring_device_payload (monitoring_device_id, payload_type, direction)
);

CREATE TABLE message_routes (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    message_payload_id bigint unsigned NOT NULL,
    hop_number int unsigned NOT NULL,
    service_name varchar(255) NOT NULL,
    node_type enum(
        'CHIRPSTACK_GATEWAY',
        'CHIRPSTACK_SERVER',
        'LORARX_SERVICE',
        'LORATX_SERVICE',
        'THINGSBOARD_MQTT',
        'THINGSBOARD_SERVER'
    ) NOT NULL,
    connection_type enum('HTTP', 'MQTT', 'INTERNAL') NOT NULL,
    status enum('RECEIVED', 'PROCESSED', 'FORWARDED', 'DELIVERED', 'FAILED') NOT NULL,
    timestamp timestamp NOT NULL,
    metadata json,
    created_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (message_payload_id) REFERENCES message_payloads(id),
    INDEX idx_message_route (message_payload_id, hop_number)
);

-- Example Heartbeat Message with Route
INSERT INTO message_payloads (
    monitoring_device_id,
    message_id,
    payload_type,
    direction,
    payload
) VALUES (
    1, -- RX Monitor Device ID
    'HB_123456789',
    'HEARTBEAT',
    'UPLINK',
    '{
        "device_id": "rx_monitor_dev1",
        "timestamp": "2024-01-20T10:00:00Z",
        "type": "heartbeat",
        "data": {
            "sequence": 1,
            "battery": 3.6,
            "rssi": -80,
            "snr": 9.5
        }
    }'
);

-- Example Route Tracking
INSERT INTO message_routes (
    message_payload_id,
    hop_number,
    service_name,
    node_type,
    connection_type,
    status,
    timestamp,
    metadata
) VALUES
(1, 1, 'gateway-1', 'CHIRPSTACK_GATEWAY', 'MQTT', 'RECEIVED', '2024-01-20T10:00:01Z',
    '{
        "gateway_id": "gateway-1",
        "rssi": -80,
        "snr": 9.5,
        "channel": 1,
        "frequency": 868.1,
        "mqtt_topic": "gateway/gateway-1/rx"
    }'
),
(1, 2, 'star1.likem13.com', 'CHIRPSTACK_SERVER', 'INTERNAL', 'PROCESSED', '2024-01-20T10:00:02Z',
    '{
        "application_id": "1",
        "device_eui": "DEVICE_EUI_1",
        "f_cnt": 123
    }'
),
(1, 3, 'lorarx-service-1', 'LORARX_SERVICE', 'HTTP', 'FORWARDED', '2024-01-20T10:00:03Z',
    '{
        "service_id": "rx1",
        "queue": "uplink_queue",
        "processing_time": 15,
        "http_endpoint": "/api/v1/uplink",
        "http_status": 200
    }'
),
(1, 4, 'things.likem13.com', 'THINGSBOARD_MQTT', 'MQTT', 'DELIVERED', '2024-01-20T10:00:04Z',
    '{
        "topic": "v1/devices/me/telemetry",
        "qos": 1,
        "retained": false,
        "mqtt_client_id": "lorarx-service-1"
    }'
);

## Example Analysis Queries

### 1. End-to-End Message Latency
```sql
-- Calculate end-to-end latency for each message
SELECT 
    mp.message_id,
    mp.payload_type,
    MIN(mr.timestamp) as start_time,
    MAX(mr.timestamp) as end_time,
    TIMESTAMPDIFF(MILLISECOND, MIN(mr.timestamp), MAX(mr.timestamp)) as total_latency_ms,
    COUNT(mr.id) as hop_count
FROM message_payloads mp
JOIN message_routes mr ON mp.id = mr.message_payload_id
GROUP BY mp.id, mp.message_id, mp.payload_type
HAVING total_latency_ms > 1000  -- Find messages taking more than 1 second
ORDER BY total_latency_ms DESC;
```

### 2. Performance by Connection Type
```sql
-- Average latency and success rate by connection type
WITH hop_latency AS (
    SELECT 
        mr1.message_payload_id,
        mr1.connection_type,
        mr1.hop_number,
        TIMESTAMPDIFF(MILLISECOND, mr1.timestamp, mr2.timestamp) as hop_latency_ms
    FROM message_routes mr1
    JOIN message_routes mr2 
        ON mr1.message_payload_id = mr2.message_payload_id 
        AND mr1.hop_number = mr2.hop_number - 1
)
SELECT 
    connection_type,
    COUNT(*) as total_hops,
    AVG(hop_latency_ms) as avg_latency_ms,
    MIN(hop_latency_ms) as min_latency_ms,
    MAX(hop_latency_ms) as max_latency_ms,
    COUNT(CASE WHEN hop_latency_ms > 1000 THEN 1 END) as slow_hops_count
FROM hop_latency
GROUP BY connection_type
ORDER BY avg_latency_ms DESC;
```

### 3. Service Performance Analysis
```sql
-- Performance metrics by service and connection type
SELECT 
    mr.service_name,
    mr.node_type,
    mr.connection_type,
    COUNT(*) as total_messages,
    COUNT(CASE WHEN mr.status = 'FAILED' THEN 1 END) as failed_count,
    ROUND(COUNT(CASE WHEN mr.status = 'FAILED' THEN 1 END) * 100.0 / COUNT(*), 2) as failure_rate,
    AVG(JSON_EXTRACT(mr.metadata, '$.processing_time')) as avg_processing_time_ms
FROM message_routes mr
WHERE mr.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY mr.service_name, mr.node_type, mr.connection_type
HAVING total_messages > 100
ORDER BY failure_rate DESC;
```

### 4. MQTT vs HTTP Performance Comparison
```sql
-- Compare performance metrics between MQTT and HTTP
WITH connection_stats AS (
    SELECT 
        mp.payload_type,
        mr.connection_type,
        COUNT(*) as message_count,
        AVG(CASE 
            WHEN mr.connection_type = 'MQTT' THEN JSON_EXTRACT(mr.metadata, '$.qos')
            WHEN mr.connection_type = 'HTTP' THEN JSON_EXTRACT(mr.metadata, '$.http_status')
        END) as quality_metric,
        COUNT(CASE WHEN mr.status = 'FAILED' THEN 1 END) as failures,
        AVG(CASE 
            WHEN mr.node_type IN ('LORARX_SERVICE', 'LORATX_SERVICE') 
            THEN JSON_EXTRACT(mr.metadata, '$.processing_time')
        END) as avg_processing_time_ms
    FROM message_payloads mp
    JOIN message_routes mr ON mp.id = mr.message_payload_id
    WHERE mr.connection_type IN ('MQTT', 'HTTP')
    AND mr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY mp.payload_type, mr.connection_type
)
SELECT 
    payload_type,
    connection_type,
    message_count,
    quality_metric,
    ROUND(failures * 100.0 / message_count, 2) as failure_rate_percent,
    ROUND(avg_processing_time_ms, 2) as avg_processing_time_ms
FROM connection_stats
ORDER BY payload_type, message_count DESC;
```

### 5. Bottleneck Detection
```sql
-- Identify potential bottlenecks in the message route
WITH hop_times AS (
    SELECT 
        mr1.message_payload_id,
        mr1.hop_number,
        mr1.service_name,
        mr1.node_type,
        mr1.connection_type,
        TIMESTAMPDIFF(MILLISECOND, mr1.timestamp, mr2.timestamp) as hop_duration_ms
    FROM message_routes mr1
    JOIN message_routes mr2 
        ON mr1.message_payload_id = mr2.message_payload_id 
        AND mr1.hop_number = mr2.hop_number - 1
    WHERE mr1.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
)
SELECT 
    service_name,
    node_type,
    connection_type,
    COUNT(*) as hop_count,
    AVG(hop_duration_ms) as avg_duration_ms,
    MAX(hop_duration_ms) as max_duration_ms,
    MIN(hop_duration_ms) as min_duration_ms,
    STDDEV(hop_duration_ms) as stddev_duration_ms,
    COUNT(CASE WHEN hop_duration_ms > 1000 THEN 1 END) as slow_hops
FROM hop_times
GROUP BY service_name, node_type, connection_type
HAVING avg_duration_ms > 100  -- Focus on hops taking more than 100ms on average
ORDER BY avg_duration_ms DESC;
```

### Usage in PHP
```php
class MessageAnalytics
{
    public function getEndToEndLatencyStats(): Collection
    {
        return DB::select("
            SELECT 
                mp.message_id,
                mp.payload_type,
                MIN(mr.timestamp) as start_time,
                MAX(mr.timestamp) as end_time,
                TIMESTAMPDIFF(MILLISECOND, MIN(mr.timestamp), MAX(mr.timestamp)) as total_latency_ms,
                COUNT(mr.id) as hop_count
            FROM message_payloads mp
            JOIN message_routes mr ON mp.id = mr.message_payload_id
            GROUP BY mp.id, mp.message_id, mp.payload_type
            HAVING total_latency_ms > 1000
            ORDER BY total_latency_ms DESC
            LIMIT 100
        ");
    }

    public function getConnectionTypePerformance(): Collection
    {
        return DB::select("
            WITH hop_latency AS (
                SELECT 
                    mr1.message_payload_id,
                    mr1.connection_type,
                    mr1.hop_number,
                    TIMESTAMPDIFF(MILLISECOND, mr1.timestamp, mr2.timestamp) as hop_latency_ms
                FROM message_routes mr1
                JOIN message_routes mr2 
                    ON mr1.message_payload_id = mr2.message_payload_id 
                    AND mr1.hop_number = mr2.hop_number - 1
            )
            SELECT 
                connection_type,
                COUNT(*) as total_hops,
                AVG(hop_latency_ms) as avg_latency_ms,
                MIN(hop_latency_ms) as min_latency_ms,
                MAX(hop_latency_ms) as max_latency_ms,
                COUNT(CASE WHEN hop_latency_ms > 1000 THEN 1 END) as slow_hops_count
            FROM hop_latency
            GROUP BY connection_type
            ORDER BY avg_latency_ms DESC
        ");
    }
}
```

## Verwendung im Code

### Server Configuration abrufen
```php
// In ThingsBoardMonitor
public function __construct(Server $server)
{
    $this->server = $server;
    $this->settings = $server->settings;
    $this->credentials = $server->credentials;
    
    $this->mqttClient = new MQTTClient(
        host: $this->settings['host'],
        port: $this->settings['port'],
        ssl: $this->settings['ssl'],
        accessToken: $this->credentials['access_token']
    );
}
```

### Health Status aktualisieren
```php
public function updateHealthStatus(string $status, ?string $errorMessage = null, ?array $metrics = null): void
{
    ServerHealthStatus::create([
        'server_id' => $this->server->id,
        'status' => $status,
        'last_check' => now(),
        'error_message' => $errorMessage,
        'metrics' => $metrics
    ]);
}
```

### Monitoring Logs schreiben
```php
public function log(string $eventType, string $message, ?array $context = null): void
{
    MonitoringLog::create([
        'server_id' => $this->server->id,
        'event_type' => $eventType,
        'message' => $message,
        'context' => $context
    ]);
}
```

### Monitoring Device erstellen
```php
// In MonitoringDeviceService
public function createMonitoringDevice(array $data): MonitoringDevice
{
    return MonitoringDevice::create([
        'name' => $data['name'],
        'description' => $data['description'],
        'thingsboard_server_id' => $data['thingsboard_server_id'],
        'chirpstack_server_id' => $data['chirpstack_server_id'],
        'device_type' => $data['device_type'],
        'settings' => $data['settings'],
        'credentials' => $data['credentials']
    ]);
}
```

### Device Status aktualisieren
```php
// In DeviceMonitor
public function updateDeviceStatus(
    string $status, 
    ?string $messageType = null,
    ?int $latency = null,
    ?string $errorMessage = null,
    ?array $metrics = null
): void
{
    DeviceStatus::create([
        'monitoring_device_id' => $this->device->id,
        'status' => $status,
        'last_check' => now(),
        'message_type' => $messageType,
        'latency' => $latency,
        'error_message' => $errorMessage,
        'metrics' => $metrics
    ]);
}
```

### Device Monitoring Log schreiben
```php
public function logMessage(
    string $eventType,
    string $direction,
    string $message,
    ?array $context = null
): void
{
    DeviceMonitoringLog::create([
        'monitoring_device_id' => $this->device->id,
        'event_type' => $eventType,
        'direction' => $direction,
        'message' => $message,
        'context' => $context
    ]);
}
```

### Message Payload erstellen
```php
// In MessageTrackingService
public function createMessagePayload(
    MonitoringDevice $device,
    string $messageId,
    string $payloadType,
    string $direction,
    array $payload
): MessagePayload {
    return MessagePayload::create([
        'monitoring_device_id' => $device->id,
        'message_id' => $messageId,
        'payload_type' => $payloadType,
        'direction' => $direction,
        'payload' => $payload
    ]);
}

public function trackMessageHop(
    MessagePayload $messagePayload,
    int $hopNumber,
    string $serviceName,
    string $nodeType,
    string $connectionType,
    string $status,
    array $metadata = []
): MessageRoute {
    return MessageRoute::create([
        'message_payload_id' => $messagePayload->id,
        'hop_number' => $hopNumber,
        'service_name' => $serviceName,
        'node_type' => $nodeType,
        'connection_type' => $connectionType,
        'status' => $status,
        'timestamp' => now(),
        'metadata' => $metadata
    ]);
}

// Example Usage
public function trackHeartbeatMessage(MonitoringDevice $device, array $payload): void {
    $messagePayload = $this->createMessagePayload(
        device: $device,
        messageId: 'HB_' . time(),
        payloadType: 'HEARTBEAT',
        direction: 'UPLINK',
        payload: $payload
    );

    // Track each hop in the message route
    $this->trackMessageHop(
        messagePayload: $messagePayload,
        hopNumber: 1,
        serviceName: 'gateway-1',
        nodeType: 'CHIRPSTACK_GATEWAY',
        connectionType: 'MQTT',
        status: 'RECEIVED',
        metadata: [
            'rssi' => -80,
            'snr' => 9.5,
            'mqtt_topic' => 'gateway/gateway-1/rx'
        ]
    );

    // Continue tracking subsequent hops...
}
