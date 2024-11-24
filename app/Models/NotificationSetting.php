<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $notification_type_id
 * @property array $configuration
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\DeviceMonitoringResult|null $lastResult
 * @property-read \App\Models\NotificationType $notificationType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestScenario> $testScenarios
 * @property-read int|null $test_scenarios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereNotificationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting withoutTrashed()
 * @mixin \Eloquent
 */
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
