<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestScenario extends Model
{
    protected $fillable = [
        'name',
        'description',
        'mqtt_device_id',
        'http_device_id',
        'is_active',
        'interval_seconds',
        'timeout_seconds',
        'max_retries',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'interval_seconds' => 'integer',
        'timeout_seconds' => 'integer',
        'max_retries' => 'integer',
        'thingsboard_last_success_at' => 'datetime',
        'thingsboard_success_rate_1h' => 'float',
        'thingsboard_success_rate_24h' => 'float',
        'thingsboard_messages_count_1h' => 'integer',
        'thingsboard_messages_count_24h' => 'integer',
        'chirpstack_last_success_at' => 'datetime',
        'chirpstack_success_rate_1h' => 'float',
        'chirpstack_success_rate_24h' => 'float',
        'chirpstack_messages_count_1h' => 'integer',
        'chirpstack_messages_count_24h' => 'integer',
        'mqtt_last_success_at' => 'datetime',
        'mqtt_success_rate_1h' => 'float',
        'mqtt_success_rate_24h' => 'float',
        'mqtt_messages_count_1h' => 'integer',
        'mqtt_messages_count_24h' => 'integer',
        'loratx_last_success_at' => 'datetime',
        'loratx_success_rate_1h' => 'float',
        'loratx_success_rate_24h' => 'float',
        'loratx_messages_count_1h' => 'integer',
        'loratx_messages_count_24h' => 'integer',
        'lorarx_last_success_at' => 'datetime',
        'lorarx_success_rate_1h' => 'float',
        'lorarx_success_rate_24h' => 'float',
        'lorarx_messages_count_1h' => 'integer',
        'lorarx_messages_count_24h' => 'integer',
    ];

    // Status constants
    const STATUS_HEALTHY = 'HEALTHY';
    const STATUS_WARNING = 'WARNING';
    const STATUS_CRITICAL = 'CRITICAL';

    // Relationships
    public function mqttDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'mqtt_device_id');
    }

    public function httpDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'http_device_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function notificationSettings(): BelongsToMany
    {
        return $this->belongsToMany(NotificationSetting::class, 'test_scenario_notification_settings')
            ->withPivot(['last_notification_at', 'last_result_id'])
            ->withTimestamps();
    }

    public function notificationTypes(): BelongsToMany
    {
        return $this->belongsToMany(NotificationType::class, 'test_scenario_notifications')
            ->withPivot(['warning_threshold', 'critical_threshold', 'min_downtime_minutes'])
            ->withTimestamps();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'expected_values' => [
                'min_value' => 0,
                'max_value' => 100,
                'expected_format' => 'number',
            ],
            'validation_rules' => [
                'response_time_ms' => 5000,
                'required_fields' => [],
            ],
        ];
    }

    public function updateSuccessRate(bool $success): void
    {
        $this->last_success_at = $success ? now() : $this->last_success_at;
        
        // Update hourly metrics
        $hourlyResults = $this->results()
            ->where('created_at', '>=', now()->subHour())
            ->count();
        
        $hourlySuccesses = $this->results()
            ->where('created_at', '>=', now()->subHour())
            ->where('status', 'success')
            ->count();
            
        // Update daily metrics
        $dailyResults = $this->results()
            ->where('created_at', '>=', now()->subDay())
            ->count();
            
        $dailySuccesses = $this->results()
            ->where('created_at', '>=', now()->subDay())
            ->where('status', 'success')
            ->count();
        
        $this->messages_count_1h = $hourlyResults;
        $this->messages_count_24h = $dailyResults;
        $this->success_rate_1h = $hourlyResults > 0 ? ($hourlySuccesses / $hourlyResults) * 100 : null;
        $this->success_rate_24h = $dailyResults > 0 ? ($dailySuccesses / $dailyResults) * 100 : null;
        
        $this->save();
    }

    public function hasWarningCondition(): bool
    {
        return ($this->success_rate_24h !== null && $this->success_rate_24h < 90) ||
               ($this->success_rate_1h !== null && $this->success_rate_1h < 85);
    }

    public function hasCriticalCondition(): bool
    {
        return ($this->success_rate_24h !== null && $this->success_rate_24h < 75) ||
               ($this->success_rate_1h !== null && $this->success_rate_1h < 70) ||
               ($this->last_success_at !== null && $this->last_success_at->diffInMinutes() > 30);
    }

    /**
     * Check if any notification thresholds are exceeded
     */
    public function checkNotificationThresholds(): array
    {
        $notifications = [];
        $successRate = $this->success_rate_1h ?? 100;

        foreach ($this->notificationTypes as $notificationType) {
            $pivot = $notificationType->pivot;
            
            if ($successRate <= $pivot->critical_threshold) {
                $notifications[] = [
                    'type' => $notificationType,
                    'level' => 'critical',
                    'threshold' => $pivot->critical_threshold,
                    'current' => $successRate
                ];
            } elseif ($successRate <= $pivot->warning_threshold) {
                $notifications[] = [
                    'type' => $notificationType,
                    'level' => 'warning',
                    'threshold' => $pivot->warning_threshold,
                    'current' => $successRate
                ];
            }
        }

        return $notifications;
    }
}
