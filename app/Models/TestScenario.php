<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $device_id
 * @property string $test_type
 * @property array|null $test_configuration
 * @property int $interval_seconds
 * @property int $timeout_seconds
 * @property int $max_retries
 * @property bool $is_active
 * @property array|null $notification_settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeviceMonitoringResult> $monitoringResults
 * @property-read int|null $monitoring_results_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationSetting> $notificationSettings
 * @property-read int|null $notification_settings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereIntervalSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMaxRetries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereNotificationSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereTestConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereTestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereTimeoutSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
