# System Requirements and Dependencies

## System Requirements

### Server Requirements
- PHP >= 8.1
- MySQL >= 8.0 or MariaDB >= 10.3
- Cron service
- Node.js >= 16.0 (for WebSocket support if needed)

### PHP Extensions
- ext-json
- ext-pdo
- ext-sockets
- ext-openssl
- ext-mbstring
- ext-curl

### Server Specifications
- Minimum 2 CPU cores
- 4GB RAM minimum (8GB recommended)
- 20GB SSD storage
- Network bandwidth: 100Mbps minimum

## Required Packages

### Composer Packages
```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "php-mqtt/client": "^1.8",
        "sammyjo20/saloon": "^2.0",
        "sammyjo20/saloon-laravel": "^2.0",
        "ramsey/uuid": "^4.7",
        "monolog/monolog": "^3.4",
        "symfony/process": "^6.3",
        "spatie/laravel-query-builder": "^5.3",
        "doctrine/dbal": "^3.7"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.1",
        "mockery/mockery": "^1.6",
        "fakerphp/faker": "^1.23",
        "nunomaduro/collision": "^7.8",
        "phpstan/phpstan": "^1.10",
        "laravel/pint": "^1.13"
    }
}
```

### NPM Packages (if WebSocket support is needed)
```json
{
    "dependencies": {
        "socket.io": "^4.7.2",
        "socket.io-client": "^4.7.2",
        "ws": "^8.14.2"
    }
}
```

## Key Package Purposes

### MQTT Communication
- **php-mqtt/client**: Handles MQTT protocol communication with ChirpStack and ThingsBoard
  - Features: QoS levels, SSL/TLS support, automatic reconnection
  - Used for: Device telemetry, commands, and status updates

### HTTP Communication
- **sammyjo20/saloon**: Handles HTTP/REST API communications
  - Features: OAuth2 support, response caching, retry handling
  - Used for: API integration with ThingsBoard and ChirpStack
- **sammyjo20/saloon-laravel**: Laravel integration for Saloon
  - Features: Laravel service provider, facade, testing helpers

### Monitoring and Logging
- **monolog/monolog**: Advanced logging capabilities
  - Features: Multiple handlers, formatters, processors
  - Used for: System logging, error tracking, audit trails

### Development Tools
- **phpstan/phpstan**: Static analysis tool
- **laravel/pint**: Code style fixer
- **phpunit/phpunit**: Testing framework

## Environment Setup

### Required Environment Variables
```env
# Application
APP_NAME=HeartBeat
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=heartbeat
DB_USERNAME=heartbeat
DB_PASSWORD=secure_password

# MQTT Default Settings
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_CLIENT_ID=heartbeat-monitor
MQTT_USERNAME=null
MQTT_PASSWORD=null
MQTT_CLEAN_SESSION=true
```

### Directory Permissions
```bash
# Storage and cache directories should be writable
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Ensure proper ownership
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

## Cron Configuration

### System Crontab Setup
Add these entries to your system's crontab (`crontab -e`):

```bash
# Run the scheduler every minute
* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1

# Monitor ThingsBoard instances every 5 minutes
*/5 * * * * cd /path/to/your/app && php artisan monitor:thingsboard >> /dev/null 2>&1

# Monitor ChirpStack instances every 5 minutes
*/5 * * * * cd /path/to/your/app && php artisan monitor:chirpstack >> /dev/null 2>&1

# Clean up old monitoring logs daily
0 0 * * * cd /path/to/your/app && php artisan monitor:cleanup --days=30 >> /dev/null 2>&1
```

### Laravel Task Scheduler
Configure the tasks in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Monitor ThingsBoard instances
    $schedule->command('monitor:thingsboard')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/thingsboard-monitor.log'));

    // Monitor ChirpStack instances
    $schedule->command('monitor:chirpstack')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/chirpstack-monitor.log'));

    // Cleanup old logs
    $schedule->command('monitor:cleanup --days=30')
            ->daily()
            ->at('00:00')
            ->appendOutputTo(storage_path('logs/cleanup.log'));
}
```

## Installation Steps

1. **System Dependencies**
```bash
# Update package list
apt-get update

# Install system requirements
apt-get install -y \
    php8.1-cli \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-curl \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-zip \
    nodejs \
    npm \
    cron

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

2. **Application Setup**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies (if needed)
npm install --production

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Verify Cron Setup**
```bash
# Check if cron is running
systemctl status cron

# View current crontab
crontab -l

# Edit crontab
crontab -e
```

## Performance Tuning

### PHP Configuration
```ini
# php.ini optimizations
memory_limit = 512M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 32531
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1
```

### MySQL Configuration
```ini
# my.cnf optimizations
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
```

## Monitoring Commands

Create these Artisan commands for the monitoring tasks:

1. **ThingsBoard Monitoring Command**
```php
// app/Console/Commands/MonitorThingsBoard.php
class MonitorThingsBoard extends Command
{
    protected $signature = 'monitor:thingsboard';
    protected $description = 'Monitor ThingsBoard instances';

    public function handle()
    {
        // Implementation
    }
}
```

2. **ChirpStack Monitoring Command**
```php
// app/Console/Commands/MonitorChirpStack.php
class MonitorChirpStack extends Command
{
    protected $signature = 'monitor:chirpstack';
    protected $description = 'Monitor ChirpStack instances';

    public function handle()
    {
        // Implementation
    }
}
```

3. **Cleanup Command**
```php
// app/Console/Commands/CleanupMonitoringLogs.php
class CleanupMonitoringLogs extends Command
{
    protected $signature = 'monitor:cleanup {--days=30}';
    protected $description = 'Clean up old monitoring logs';

    public function handle()
    {
        // Implementation
    }
}
