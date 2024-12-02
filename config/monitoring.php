<?php

use App\Enums\ServiceType;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | These values are used as default settings for monitoring various services.
    |
    */
    'defaults' => [
        'check_interval' => env('MONITORING_CHECK_INTERVAL', 300), // 5 minutes
        'timeout' => env('MONITORING_TIMEOUT', 30), // 30 seconds
        'retry_attempts' => env('MONITORING_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('MONITORING_RETRY_DELAY', 60), // 1 minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the alert system including cooldown periods and
    | notification settings.
    |
    */
    'alerts' => [
        'cooldown' => env('ALERT_COOLDOWN', 900), // 15 minutes
        'batch_size' => env('ALERT_BATCH_SIZE', 100),
        'notification_channels' => [
            'mail' => [
                'enabled' => env('ALERT_MAIL_ENABLED', true),
                'queue' => env('ALERT_MAIL_QUEUE', 'notifications'),
            ],
            'slack' => [
                'enabled' => env('ALERT_SLACK_ENABLED', false),
                'webhook' => env('ALERT_SLACK_WEBHOOK'),
                'queue' => env('ALERT_SLACK_QUEUE', 'notifications'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring-specific logging, including channels for
    | different types of logs and external services like Sentry.
    |
    */
    'logging' => [
        'channel' => env('MONITORING_LOG_CHANNEL', 'monitoring'),
        'sentry' => [
            'enabled' => env('MONITORING_SENTRY_ENABLED', false),
            'dsn' => env('MONITORING_SENTRY_DSN'),
            'level' => env('MONITORING_SENTRY_LEVEL', 'error'),
            'traces_sample_rate' => env('MONITORING_SENTRY_TRACES_SAMPLE_RATE', 1.0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Configuration settings specific to each supported IoT platform.
    |
    */
    'services' => [
        ServiceType::THINGSBOARD->value => [
            'api_version' => env('THINGSBOARD_API_VERSION', 'v1'),
            'timeout' => env('THINGSBOARD_TIMEOUT', 30),
            'verify_ssl' => env('THINGSBOARD_VERIFY_SSL', true),
        ],
        ServiceType::CHIRPSTACK->value => [
            'api_version' => env('CHIRPSTACK_API_VERSION', 'v3'),
            'timeout' => env('CHIRPSTACK_TIMEOUT', 30),
            'verify_ssl' => env('CHIRPSTACK_VERIFY_SSL', true),
        ],
        ServiceType::MQTT_TB->value => [
            'threshold' => 0.9,
            'timeout' => 60,
        ],
        ServiceType::MQTT_CS->value => [
            'threshold' => 0.9,
            'timeout' => 60,
        ],
        ServiceType::LORATX->value => [
            'threshold' => 0.9,
            'timeout' => 60,
        ],
        ServiceType::LORARX->value => [
            'threshold' => 0.9,
            'timeout' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Thresholds
    |--------------------------------------------------------------------------
    |
    | Configuration for service status determination thresholds
    |
    */
    'status' => [
        'critical_downtime_minutes' => env('STATUS_CRITICAL_DOWNTIME_MINUTES', 10),
        'warning_success_rate' => env('STATUS_WARNING_SUCCESS_RATE', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Certificate Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring SSL certificates of the services.
    |
    */
    'ssl' => [
        'enabled' => env('SSL_MONITORING_ENABLED', true),
        'warning_days' => env('SSL_WARNING_DAYS', 30),
        'critical_days' => env('SSL_CRITICAL_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for various health checks.
    |
    */
    'health_checks' => [
        'response_time' => [
            'warning' => env('RESPONSE_TIME_WARNING', 1000), // 1 second
            'critical' => env('RESPONSE_TIME_CRITICAL', 5000), // 5 seconds
        ],
        'error_rate' => [
            'window' => env('ERROR_RATE_WINDOW', 3600), // 1 hour
            'threshold' => env('ERROR_RATE_THRESHOLD', 0.05), // 5%
        ],
    ],
];
