<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestScenario extends Model
{
    protected $fillable = [
        'name',
        'description',
        'device_id',
        'test_type',
        'test_configuration',
        'interval_seconds',
        'timeout_seconds',
        'max_retries',
        'is_active',
        'notification_settings',
    ];

    protected $casts = [
        'test_configuration' => 'array',
        'notification_settings' => 'array',
        'is_active' => 'boolean',
        'interval_seconds' => 'integer',
        'timeout_seconds' => 'integer',
        'max_retries' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function monitoringResults(): HasMany
    {
        return $this->hasMany(DeviceMonitoringResult::class);
    }

    public function notificationSettings(): MorphMany
    {
        return $this->morphMany(NotificationSetting::class, 'notifiable');
    }

    public function getTestTypes(): array
    {
        return [
            'mqtt_rx' => 'MQTT Receive Test',
            'mqtt_tx' => 'MQTT Transmit Test',
            'http_health' => 'HTTP Health Check',
            'http_telemetry' => 'HTTP Telemetry Test',
            'http_rpc' => 'HTTP RPC Test',
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return match($this->test_type) {
            'mqtt_rx' => [
                'expected_message_count' => 1,
                'message_timeout' => 30,
            ],
            'mqtt_tx' => [
                'message_payload' => 'test',
                'expect_ack' => true,
            ],
            'http_health' => [
                'expected_status' => 200,
            ],
            'http_telemetry' => [
                'data_points' => ['temperature', 'humidity'],
            ],
            'http_rpc' => [
                'method' => 'getValue',
                'params' => [],
            ],
            default => [],
        };
    }
}
