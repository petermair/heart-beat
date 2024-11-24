# Heart-Beat Service Implementation Todo

## Completed Tasks 

### MQTT Implementation
- [x] Base MQTT Client implementation
- [x] ChirpStack MQTT Client
  - [x] Uplink message handling
  - [x] Downlink message handling
  - [x] Join event handling
  - [x] ACK handling
  - [x] Error handling
- [x] ThingsBoard MQTT Client
  - [x] Telemetry data sending
  - [x] Attribute updates
  - [x] RPC request handling
  - [x] Status reporting
- [x] MQTT Monitor implementation
  - [x] RX path monitoring
  - [x] TX path monitoring
  - [x] Health monitoring

### HTTP Implementation
- [x] ThingsBoard HTTP Connector
  - [x] Authentication handling
  - [x] Default configurations
  - [x] Token management
- [x] Basic ThingsBoard Requests
  - [x] Login request
  - [x] Health check request
  - [x] System info request
  - [x] System metrics request
  - [x] Active sessions request
- [x] Device Management Requests
  - [x] List devices (with pagination)
  - [x] Get single device
  - [x] Create device
  - [x] Update device
  - [x] Delete device
- [x] Monitoring-Specific Requests
  - [x] Telemetry HTTP request
  - [x] RPC HTTP request
  - [x] Health check request
- [x] ChirpStack HTTP Integration
  - [x] Base connector with authentication
  - [x] Device queue request for downlinks
  - [x] Device messages request
  - [x] Device status request
  - [x] Device info request
- [x] ThingsBoard HTTP Integration
  - [x] Base connector with authentication
  - [x] Device telemetry request
  - [x] Device RPC request
  - [x] Device status request
  - [x] Device list request
  - [x] Device create request

### Data Transfer Objects
- [x] ChirpStack Message DTO
- [x] ThingsBoard Message DTO
- [x] Message Route DTO
- [x] Message Payload DTO

### Development Tools & Quality Assurance
- [x] Error Tracking & Monitoring
  - [x] Sentry Integration
    - [x] Configure error grouping
    - [x] Set up environment-specific tracking
    - [x] Add custom context for MQTT/HTTP errors
    - [x] Configure performance monitoring
    - [x] Set up error notifications
- [x] Code Quality
  - [x] PHPStan
    - [x] Configure static analysis
    - [x] Set maximum level
    - [x] Add custom rules

### Administration Dashboard
- [x] Filament Setup
  - [x] Install and configure Filament
  - [x] Set up authentication
  - [x] Configure theme and branding
- [x] Instance Management
  - [x] Server Resource
    - [x] CRUD operations
    - [x] Basic validation
    - [x] Dynamic credentials based on server type
    - [x] Connection testing
  - [x] MQTT Broker Resource
    - [x] CRUD operations
    - [x] Basic validation
- [x] Device Management
  - [x] Device Resource
    - [x] CRUD operations
    - [x] Server configuration
    - [x] Communication type selection
    - [x] Monitoring settings
  - [x] Device Monitoring
    - [x] Manual testing through UI
    - [x] Response time tracking
    - [x] Success rate calculation
    - [x] Metadata support
    - [x] Test scenario integration
  - [x] Monitoring Results
    - [x] Detailed monitoring history
    - [x] Success/failure status tracking
    - [x] Response times for both platforms
    - [x] Error message logging
    - [x] Additional metadata storage
- [x] Instance Management
  - [x] Server Management
    - [x] Advanced ThingsBoard features
      - [x] Device management
      - [x] Credentials management
    - [x] Advanced ChirpStack features
      - [x] Application management
      - [x] Device management
      - [x] API key management
- [x] Test Configuration
  - [x] Test Scenario Resource
    - [x] Configure test types
    - [x] Set intervals and timeouts
    - [x] Manage retries
    - [x] Set up notifications
  - [x] Instance Pairing
    - [x] Link ThingsBoard and ChirpStack instances
    - [x] Configure routing paths
    - [x] Set up test devices
- [x] Monitoring Dashboard
  - [x] System Overview Page
    - [x] Health status summary
    - [x] Active tests count
    - [x] Error rates
    - [x] Performance metrics
  - [x] Instance Details Page
    - [x] Instance health status
    - [x] Test history
    - [x] Response times
    - [x] Error logs
  - [x] Test Results Page
    - [x] Test execution history
    - [x] Success/failure rates
    - [x] Response time trends
    - [x] Error details

## Pending Tasks 

### Administration Dashboard
- [ ] Notification System
  - [ ] Alert Configuration
    - [ ] Define alert conditions
    - [ ] Set thresholds
    - [ ] Configure recipients
  - [ ] Notification Channels
    - [ ] Email notifications
    - [ ] Slack integration
    - [ ] Webhook support

- [ ] Reporting
  - [ ] System Reports
    - [ ] Health status reports
    - [ ] Performance reports
    - [ ] Error reports
  - [ ] Export Options
    - [ ] CSV export
    - [ ] PDF reports
    - [ ] API access

### HTTP Implementation
- [ ] ChirpStack Requests
  - [ ] Application management
  - [ ] Device profiles
  - [ ] Network server configuration
  - [ ] Gateway management
- [ ] ThingsBoard Advanced Requests
  - [ ] Asset management
  - [ ] Customer management
  - [ ] Dashboard management
  - [ ] Rule chain management

### Monitoring Implementation
- [ ] Test Flows Implementation
  - [ ] Main Flows
    - [ ] Flow 1: TB → MQTT → LoraTX → MQTT → CS
    - [ ] Flow 2: CS → MQTT → LoraRX → MQTT → TB
    - [ ] Flow 3: Two-way combination of Flow 1 & 2
  - [ ] Direct Test Flows
    - [ ] Flow 4: CS → MQTT → TB direct test
    - [ ] Flow 5: TB → MQTT → CS direct test
  - [ ] Health Check Flows
    - [ ] Flow 6: TB MQTT health check
    - [ ] Flow 7: CS MQTT health check
    - [ ] Flow 8: TB HTTP health check
    - [ ] Flow 9: CS HTTP health check

- [ ] Service Status Monitoring
  - [ ] Critical Alert System (10min downtime)
    - [ ] Service status tracking
    - [ ] Immediate notifications
    - [ ] Downtime duration tracking
  - [ ] Warning Alert System (60min success rate)
    - [ ] Success rate calculation
    - [ ] Hourly checks
    - [ ] Alert threshold management (90%)
  - [ ] Service Health Dashboard
    - [ ] Real-time status indicators
    - [ ] Success rate display
    - [ ] Last successful message tracking

- [ ] Status Aggregation
  - [ ] Per-Service Status
    - [ ] ThingsBoard status aggregation
    - [ ] ChirpStack status aggregation
    - [ ] MQTT Broker status aggregation
    - [ ] LoraTX status aggregation
    - [ ] LoraRX status aggregation
  - [ ] Flow Status
    - [ ] Main flow status tracking
    - [ ] Direct test status tracking
    - [ ] Health check status tracking

- [ ] Visualization
  - [ ] Flow Diagrams
    - [ ] Main flows visualization
    - [ ] Status indicators
    - [ ] Success rates display
  - [ ] Service Status
    - [ ] Color-coded status indicators
    - [ ] Downtime/success rate display
    - [ ] Historical status view

- [ ] Service Detail Pages
  - [ ] ThingsBoard Details
    - [ ] Basic Information
      - [ ] Current status indicator
      - [ ] Uptime percentage (24h)
      - [ ] Last successful message
      - [ ] Current success rate
    - [ ] Active Flows Display
      - [ ] Flow 1: TB → MQTT → LoraTX → MQTT → CS
      - [ ] Flow 3: Two-way route
      - [ ] Flow 4: Direct test (CS → MQTT → TB)
      - [ ] Flow 5: Direct test (TB → MQTT → CS)
      - [ ] Flow 6: MQTT health
      - [ ] Flow 8: HTTP health
    - [ ] Statistics Section
      - [ ] Time range selector (1h, 24h, 7d)
      - [ ] Message counts (sent/received)
      - [ ] Success rates over time
      - [ ] Failure counts
      - [ ] Average response times
    - [ ] Recent Issues
      - [ ] Last 5 failures list
      - [ ] Downtime periods
      - [ ] Error messages

  - [ ] ChirpStack Details
    - [ ] Basic Information
      - [ ] Status and uptime
      - [ ] Last message info
      - [ ] Success rate
    - [ ] Active Flows Display
      - [ ] Flows 1, 2, 3, 4, 5, 7, 9
    - [ ] Statistics Section
      - [ ] Time-based statistics
      - [ ] Message metrics
    - [ ] Recent Issues
      - [ ] Failure history
      - [ ] Error tracking

  - [ ] MQTT Broker Details
    - [ ] Basic Information
      - [ ] Broker status
      - [ ] Connection metrics
    - [ ] Active Flows Display
      - [ ] All MQTT flows (1-7)
    - [ ] Statistics Section
      - [ ] Messages per topic
      - [ ] Connected clients
      - [ ] Traffic metrics
    - [ ] Recent Issues
      - [ ] Connection failures
      - [ ] Error logs

  - [ ] LoraTX/LoraRX Details
    - [ ] Basic Information
      - [ ] Service status
      - [ ] Operating metrics
    - [ ] Active Flows Display
      - [ ] LoraTX: Flow 1
      - [ ] LoraRX: Flows 2, 3
    - [ ] Statistics Section
      - [ ] Message processing stats
      - [ ] Performance metrics
    - [ ] Recent Issues
      - [ ] Processing failures
      - [ ] Error tracking

### Development Tools & Quality Assurance
- [ ] Testing Framework
  - [ ] Laravel Pest Setup
    - [ ] Configure test suites
    - [ ] Set up test database
    - [ ] Add GitHub Actions for tests
    - [ ] Write feature tests
    - [ ] Write unit tests
    - [ ] Add test coverage reporting

- [ ] Code Quality
  - [ ] Laravel Pint
    - [ ] Configure coding standards
    - [ ] Set up pre-commit hooks
    - [ ] Add to CI pipeline
    - [ ] Create custom ruleset

- [ ] Development Tools
  - [ ] Laravel Telescope
    - [ ] Configure for local development
    - [ ] Monitor MQTT messages
    - [ ] Track HTTP requests
    - [ ] Track scheduled tasks
  - [ ] Laravel Debugbar
    - [ ] Enable for local development
    - [ ] Add custom metrics for MQTT/HTTP monitoring

- [ ] Documentation
  - [ ] API Documentation
    - [ ] Install Scribe
    - [ ] Document MQTT endpoints
    - [ ] Document HTTP endpoints
    - [ ] Add usage examples
    - [ ] Generate API reference

## Implementation Guidelines 

To ensure quality and maintainability, we follow these steps for each task:
1. Implement features in the order listed in this todo
2. Review implementation
   - Code quality check
   - Feature functionality verification
3. Commit changes after successful review
4. Move to the next task in sequence

## Notes
- Priority should be given to completing the monitoring implementation
- Testing should be implemented alongside new features
- Documentation should be updated as features are completed
