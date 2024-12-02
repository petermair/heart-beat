# Test Scenario Data Analysis

## TestScenarioServiceStatus

### Data Structure
```php
test_scenario_id      // Reference to TestScenario
service_type         // Type of service (thingsboard, chirpstack, mqtt, loratx, lorarx)
status              // Current service status
last_success_at     // Timestamp of last successful test
last_failure_at     // Timestamp of last failed test
success_count_1h    // Number of successful tests in last hour
total_count_1h      // Total number of tests in last hour
success_rate_1h     // Success rate percentage in last hour
downtime_started_at // When service first went down (null if up)
```

### Status Calculation Logic

1. **Success Rate Calculation**
   - Tracked per hour window
   - `success_rate_1h = (success_count_1h / total_count_1h) * 100`
   - Reset counters every hour

2. **Status States**
   - `CRITICAL`: Service is down for 10+ minutes
     - No successful tests in last 10 minutes
     - `downtime_started_at` was set more than 10 minutes ago
   - `WARNING`: Service is degraded
     - Success rate in last hour < 90%
   - `HEALTHY`: Service operating normally
     - Success rate >= 90%
     - No extended downtime (less than 10 minutes)

3. **Downtime Tracking**
   - Set `downtime_started_at` when:
     - First failure after being healthy
   - Clear `downtime_started_at` when:
     - Any successful test occurs

### Update Triggers

1. **On New Test Result**
   ```php
   // When new TestResult is created
   if (test_success) {
       last_success_at = now()
       success_count_1h++
       downtime_started_at = null
   } else {
       last_failure_at = now()
       if (!downtime_started_at) {
           downtime_started_at = now()
       }
   }
   total_count_1h++
   success_rate_1h = (success_count_1h / total_count_1h) * 100
   
   // Update status
   if (downtime_started_at && 
       downtime_started_at <= now()->subMinutes(10)) {
       status = CRITICAL
   } else if (success_rate_1h < 90) {
       status = WARNING
   } else {
       status = HEALTHY
   }
   ```

2. **Hourly Reset**
   ```php
   // Every hour
   - Reset success_count_1h to 0
   - Reset total_count_1h to 0
   - Keep other metrics intact
   ```

### Example Scenarios

1. **Healthy to Critical**
```
Initial State:
- status: HEALTHY
- success_rate_1h: 95%
- downtime_started_at: null
- last_success_at: recent

After 10+ Minutes of Failures:
- status: CRITICAL
- success_rate_1h: decreasing
- downtime_started_at: [time of first failure]
- last_success_at: >10 minutes ago
```

2. **Healthy to Warning**
```
Initial State:
- status: HEALTHY
- success_rate_1h: 95%
- downtime_started_at: null

After Intermittent Failures:
- status: WARNING
- success_rate_1h: 85%
- downtime_started_at: null (or recent if currently down)
```

3. **Critical to Healthy**
```
Initial State:
- status: CRITICAL
- success_rate_1h: 30%
- downtime_started_at: [>10 minutes ago]

After Success:
- status: HEALTHY
- success_rate_1h: gradually improving
- downtime_started_at: null
```

4. **Warning to Healthy**
```
Initial State:
- status: WARNING
- success_rate_1h: 85%
- downtime_started_at: null

After Period of Success:
- status: HEALTHY
- success_rate_1h: 95%
- downtime_started_at: null
```
