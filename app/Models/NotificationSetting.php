<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'notification_type_id',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    public function testScenarios()
    {
        return $this->belongsToMany(TestScenario::class, 'test_scenario_notification_settings')
            ->withPivot(['last_notification_at', 'last_result_id'])
            ->withTimestamps();
    }

    public function lastResult(): BelongsTo
    {
        return $this->belongsTo(DeviceMonitoringResult::class, 'last_result_id');
    }

    // Helper methods
    public function validateConfiguration(): bool
    {
        if (!$this->notificationType) {
            return false;
        }

        return $this->notificationType->validateConfiguration($this->configuration ?? []);
    }

    public function getConfigurationAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setConfigurationAttribute($value)
    {
        $this->attributes['configuration'] = json_encode($value);
    }
}
