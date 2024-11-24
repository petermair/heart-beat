<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestScenarioNotificationSetting extends Model
{
    protected $table = 'test_scenario_notification_settings';

    protected $fillable = [
        'test_scenario_id',
        'notification_setting_id',
        'last_notification_at',
        'last_result_id',
        'settings',
    ];

    protected $casts = [
        'last_notification_at' => 'datetime',
        'settings' => 'json',
    ];

    /**
     * Get the test scenario that owns this notification setting
     */
    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    /**
     * Get the notification setting
     */
    public function notificationSetting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class);
    }

    /**
     * Get the last monitoring result that triggered a notification
     */
    public function lastResult(): BelongsTo
    {
        return $this->belongsTo(DeviceMonitoringResult::class, 'last_result_id');
    }
}
