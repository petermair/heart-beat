# TODOs for IoT Heartbeat Project

## Project Goals
- [ ] IoT Platform Monitoring
  - [ ] Thingsboard server monitoring
    - [ ] HTTP/HTTPS availability check
    - [ ] Service status check
    - [ ] API endpoint health check
  - [ ] Chirpstack server monitoring
    - [ ] HTTP/HTTPS availability check
    - [ ] Service status check
    - [ ] gRPC endpoint health check
  - [ ] MQTT Infrastructure
    - [ ] Broker availability check
    - [ ] Connection test
    - [ ] Topic subscription test

- [ ] Dashboard Implementation
  - [ ] Real-time status overview
  - [ ] Historical uptime graphs
  - [ ] Service health metrics
  - [ ] Response time monitoring
  - [ ] Custom widgets for each service

- [ ] Configuration Management (Filament Admin)
  - [ ] Server management
    - [ ] Add/Edit/Delete servers
    - [ ] Configure monitoring intervals
    - [ ] Set thresholds for alerts
  - [ ] MQTT configuration
    - [ ] Broker settings
    - [ ] Authentication
    - [ ] Topic patterns
  - [ ] Alert rules configuration
    - [ ] Notification channels
    - [ ] Alert conditions
    - [ ] Escalation policies

- [ ] Notification System
  - [ ] Alert channels
    - [ ] Email notifications
    - [ ] SMS alerts
    - [ ] Webhook integration
  - [ ] Alert types
    - [ ] Service downtime
    - [ ] Performance degradation
    - [ ] Certificate expiration
    - [ ] Error rate threshold
  - [ ] Alert management
    - [ ] Acknowledgment system
    - [ ] Resolution tracking
    - [ ] Alert history

## Infrastructure Setup
- [x] Configure DNS entry for heartbeat.petermair.cloud
- [x] Deploy Apache configuration files
  - [x] Copy apache.conf to /etc/apache2/sites-available/heartbeat.petermair.cloud.conf
  - [x] Copy servername.conf to /etc/apache2/conf-available/servername.conf
  - [x] Enable Apache modules (rewrite, ssl)
  - [x] Enable site configuration (a2ensite)
  - [x] Enable servername configuration (a2enconf)
  - [x] Restart Apache

## Database Setup
- [x] Create MySQL database 'it-service-heart-beat'
- [x] Create MySQL user 'it-service-heart-beat'
- [x] Grant necessary permissions to the database user

## Application Setup
- [ ] Set up proper file permissions for Laravel
  - [ ] storage/ directory
  - [ ] bootstrap/cache/ directory
- [ ] Install application dependencies (composer install)
- [ ] Generate application key (if not already done)
- [ ] Run database migrations
- [ ] Set up proper environment variables in production

## Technical Implementation
- [ ] Core Monitoring Service
  - [ ] Create monitoring service architecture
  - [ ] Implement health check workers
  - [ ] Set up background job processing
  - [ ] Implement data collection and storage

- [ ] API Development
  - [ ] Create RESTful endpoints for monitoring data
  - [ ] Implement authentication
  - [ ] Add rate limiting
  - [ ] API documentation

## Security
- [ ] Review SSL certificate configuration
- [ ] Ensure proper file permissions
- [ ] Configure backup strategy
- [ ] Set up logging and monitoring

## Documentation
- [ ] System Architecture Documentation
  - [ ] Component diagram
  - [ ] Data flow documentation
  - [ ] API documentation
- [ ] User Documentation
  - [ ] Setup guide
  - [ ] Configuration guide
  - [ ] Alert management guide
- [ ] Developer Documentation
  - [ ] Development setup
  - [ ] Coding standards
  - [ ] Testing procedures

## Completed 
### Database and Models
- [x] Create database migrations for core tables
  - [x] `server_types` table
  - [x] `servers` table
  - [x] `mqtt_brokers` table
  - [x] `alert_rules` table
- [x] Create Eloquent models with relationships
  - [x] `ServerType` model
  - [x] `Server` model
  - [x] `MqttBroker` model
  - [x] `AlertRule` model
- [x] Create model factories for testing
  - [x] `ServerTypeFactory`
  - [x] `ServerFactory`
  - [x] `MqttBrokerFactory`
  - [x] `AlertRuleFactory`
- [x] Create unit tests for models
  - [x] `ServerTypeTest`
  - [x] `ServerTest`
  - [x] `MqttBrokerTest`
  - [x] `AlertRuleTest`

### Admin Interface
- [x] Set up Filament Admin Panel
- [x] Configure basic admin panel settings

## In Progress 
### Monitoring Services
- [ ] Create monitoring interface classes
  - [ ] Base monitoring interface
  - [ ] ThingsBoard monitor implementation
  - [ ] ChirpStack monitor implementation
- [ ] Implement monitoring logic
  - [ ] Server health checks
  - [ ] MQTT broker connectivity checks
  - [ ] SSL certificate monitoring
  - [ ] Response time monitoring

### Alert System
- [ ] Implement alert processing system
  - [ ] Alert condition evaluation
  - [ ] Alert action execution
  - [ ] Alert history tracking
- [ ] Create notification channels
  - [ ] Email notifications
  - [ ] Slack notifications
  - [ ] Webhook notifications

### Admin Interface
- [ ] Create Filament resources
  - [ ] Server management
  - [ ] MQTT broker management
  - [ ] Alert rule management
- [ ] Implement dynamic forms
  - [ ] Server configuration based on type
  - [ ] Alert rule conditions builder
  - [ ] Alert action configuration

## Future Enhancements 
### Monitoring Features
- [ ] Add support for more IoT platforms
  - [ ] AWS IoT Core
  - [ ] Azure IoT Hub
  - [ ] Google Cloud IoT
- [ ] Advanced monitoring capabilities
  - [ ] Device status monitoring
  - [ ] Message flow analysis
  - [ ] Performance metrics

### User Interface
- [ ] Create real-time dashboard
  - [ ] System status overview
  - [ ] Alert history and statistics
  - [ ] Performance metrics graphs
- [ ] Mobile-responsive design
- [ ] Dark mode support

### Integration
- [ ] API endpoints for external integration
- [ ] Webhook support for custom actions
- [ ] Integration with popular monitoring tools
  - [ ] Grafana
  - [ ] Prometheus
  - [ ] ELK Stack

### Security
- [ ] Implement role-based access control
- [ ] Add audit logging
- [ ] Secure credential storage
- [ ] API authentication

### Documentation
- [ ] API documentation
- [ ] User manual
- [ ] Developer guide
- [ ] Deployment guide

## Technical Debt 
- [ ] Optimize database queries
- [ ] Add database indexes
- [ ] Implement caching strategy
- [ ] Set up automated deployment
- [ ] Configure CI/CD pipeline
