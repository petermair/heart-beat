# Heart Beat Monitoring Service

Heart Beat is a comprehensive monitoring service designed to track and verify the health of IoT devices through both MQTT and HTTP protocols. The service supports various communication flows and provides detailed monitoring results for device status verification.

## Overview

The Heart Beat service provides:
- Real-time device monitoring
- Support for both MQTT and HTTP protocols
- Multiple test scenarios and flows
- Detailed monitoring results and metrics
- Error tracking and logging

## Getting Started

### Prerequisites
- PHP 8.1 or higher
- Laravel Framework
- MQTT Broker (for MQTT flows)
- HTTP Server (for HTTP flows)

### Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd heart-beat
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your MQTT and HTTP settings in `.env`

### Basic Usage

1. Create a test scenario:
```php
$scenario = new TestScenario();
$scenario->name = 'Basic MQTT Test';
$scenario->save();
```

2. Execute a test:
```php
$service = new TestExecutionService();
$result = $service->executeTest($scenario);
```

## Documentation Structure

- [Architecture Overview](architecture.md)
- [Monitoring System](monitoring.md)
- Flows
  - [HTTP Flows](flows/http-flows.md)
  - [MQTT Flows](flows/mqtt-flows.md)

## Contributing

Please read our contributing guidelines before submitting pull requests.

## License

[License Information]
