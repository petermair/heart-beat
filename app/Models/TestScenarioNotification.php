<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
