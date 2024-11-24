<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property int $notification_setting_id
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $last_notification_at
 * @property int|null $last_result_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DeviceMonitoringResult|null $lastResult
 * @property-read \App\Models\NotificationSetting|null $notificationSetting
 * @property-read \App\Models\TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereLastNotificationAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereLastResultId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereNotificationSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotificationSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
