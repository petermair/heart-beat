<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property array|null $configuration_schema
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NotificationSetting> $notificationSettings
 * @property-read int|null $notification_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TestScenario> $testScenarios
 * @property-read int|null $test_scenarios_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereConfigurationSchema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class NotificationType extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'configuration_schema',
        'is_active',
    ];

    protected $casts = [
        'configuration_schema' => 'json',
        'is_active' => 'boolean',
    ];

    /**
     * Get all notification settings of this type
     */
    public function notificationSettings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    /**
     * Get all test scenarios using this notification type through settings
     */
    public function testScenarios()
    {
        return $this->hasManyThrough(
            TestScenario::class,
            NotificationSetting::class,
            'notification_type_id',
            'id',
            'id',
            'id'
        );
    }

    /**
     * Validate configuration against schema
     */
    public function validateConfiguration(array $configuration): bool
    {
        if (empty($this->configuration_schema)) {
            return true;
        }

        // Basic validation that required fields are present
        foreach ($this->configuration_schema as $field => $rules) {
            if (($rules['required'] ?? false) && ! isset($configuration[$field])) {
                return false;
            }
        }

        return true;
    }
}
