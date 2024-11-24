<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TestScenario;
use App\Models\NotificationSetting;

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
            if (($rules['required'] ?? false) && !isset($configuration[$field])) {
                return false;
            }
        }

        return true;
    }
}
