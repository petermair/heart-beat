# Heart Beat Monitoring System

## Overview

The Heart Beat monitoring system provides comprehensive device health monitoring through various communication protocols. It supports both MQTT and HTTP protocols with different message formats including JSON and LPP (Cayenne Low Power Payload).

## Monitoring Flows

The system supports multiple monitoring flows, each designed to test different aspects of device communication:

### MQTT Flows (1-7)
- Flow 1: JSON to LPP
- Flow 2: LPP to JSON
- Flow 3-7: Various MQTT-specific flows

### HTTP Flows (8-9)
- Flow 8: JSON to JSON
- Flow 9: JSON to LPP

For detailed information about specific flows, see:
- [HTTP Flows Documentation](flows/http-flows.md)
- [MQTT Flows Documentation](flows/mqtt-flows.md)

## Monitoring Results

### Result Structure

Each monitoring operation creates a `DeviceMonitoringResult` record with:

```php
[
    'device_id'          => int,
    'test_scenario_id'   => int,
    'success'            => boolean,
    'error_message'      => string|null,
    'response_time_ms'   => int,
    'metadata'           => array
]
```

### Metadata Fields

The metadata varies by flow type but typically includes:
- Flow number
- Counter value
- Timestamp
- Format information
- Protocol-specific data (e.g., HTTP status code)

## Performance Monitoring

### Metrics Tracked
1. Response Time
   - Request/response round trip time
   - Processing time
   - Network latency

2. Success Rate
   - Successful tests
   - Failed tests
   - Error distribution

3. Protocol Performance
   - MQTT vs HTTP comparison
   - Format conversion overhead
   - Connection stability

### Monitoring Dashboard

The monitoring dashboard provides:
- Real-time test results
- Historical data analysis
- Error tracking
- Performance metrics
- Device status overview

## Error Handling

### Error Types
1. Connection Errors
   - Network timeouts
   - Connection refused
   - Authentication failures

2. Protocol Errors
   - Invalid message format
   - Protocol violations
   - Version mismatches

3. Device Errors
   - Device offline
   - Invalid response
   - Configuration errors

### Error Logging

All errors are logged with:
- Timestamp
- Error type
- Context information
- Stack trace (when applicable)
- Related device and scenario IDs

## Best Practices

### Test Execution
1. Regular Testing
   - Schedule periodic tests
   - Vary test scenarios
   - Monitor trends

2. Error Response
   - Implement retry logic
   - Set appropriate timeouts
   - Handle failures gracefully

3. Performance Optimization
   - Monitor response times
   - Track resource usage
   - Optimize message sizes

### Maintenance
1. Regular Reviews
   - Check error logs
   - Analyze performance metrics
   - Update test scenarios

2. System Updates
   - Keep dependencies current
   - Update device firmware
   - Maintain documentation

## Configuration

### Environment Variables
```env
# General Configuration
APP_ENV=production
APP_DEBUG=false

# MQTT Settings
MQTT_HOST=mqtt.example.com
MQTT_PORT=1883
MQTT_USERNAME=username
MQTT_PASSWORD=password

# HTTP Settings
HTTP_ENDPOINT=https://api.example.com
HTTP_TIMEOUT=30
HTTP_RETRY_ATTEMPTS=3

# Monitoring Settings
MONITORING_LOG_CHANNEL=monitoring
MONITORING_RETENTION_DAYS=30
MONITORING_MAX_RETRIES=3
```

### Device Configuration
```php
[
    'name' => 'Device Name',
    'protocol' => 'mqtt|http',
    'endpoint' => 'device_endpoint',
    'credentials' => [
        'username' => 'device_username',
        'password' => 'device_password'
    ],
    'settings' => [
        'timeout' => 30,
        'retries' => 3
    ]
]
```

## Troubleshooting

### Common Issues

1. Connection Failures
   - Check network connectivity
   - Verify credentials
   - Confirm endpoint availability

2. Timeout Errors
   - Check network latency
   - Adjust timeout settings
   - Verify device response time

3. Format Errors
   - Validate message format
   - Check protocol compatibility
   - Verify payload structure

### Debug Tools

1. Log Analysis
   - Error logs
   - Performance logs
   - System logs

2. Network Tools
   - Ping
   - Traceroute
   - Protocol analyzers

3. Monitoring Tools
   - Dashboard
   - Metrics
   - Alerts
