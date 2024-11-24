<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $test_scenario_id
 * @property int $notification_type_id
 * @property int|null $warning_threshold
 * @property int|null $critical_threshold
 * @property int|null $min_downtime_minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NotificationType $notificationType
 * @property-read \App\Models\TestScenario $testScenario
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereCriticalThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereMinDowntimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereNotificationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioNotification whereWarningThreshold($value)
 *
 * @mixin \Eloquent
 */
class TestScenarioNotification extends Model
{
    protected $table = 'test_scenario_notifications';

    protected $fillable = [
        'test_scenario_id',
        'notification_setting_id',
        'warning_threshold',
        'critical_threshold',
        'min_downtime_minutes',
    ];

    protected $casts = [
        'warning_threshold' => 'integer',
        'critical_threshold' => 'integer',
        'min_downtime_minutes' => 'integer',
    ];

    /**
     * Get the test scenario that owns this notification setting
     */
    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    /**
     * Get the notification type for this setting
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }
}
