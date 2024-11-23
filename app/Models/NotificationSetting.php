<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 
 *
 * @property int $id
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $channel
 * @property array $configuration
 * @property array|null $conditions
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $notifiable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NotificationSetting extends Model
{
    protected $fillable = [
        'channel',
        'configuration',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getAvailableChannels(): array
    {
        return [
            'email' => 'Email',
            'slack' => 'Slack',
            'webhook' => 'Webhook',
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return match($this->channel) {
            'email' => [
                'recipients' => [],
                'cc' => [],
                'bcc' => [],
            ],
            'slack' => [
                'webhook_url' => '',
                'channel' => '',
                'username' => 'Heart-Beat Monitor',
            ],
            'webhook' => [
                'url' => '',
                'method' => 'POST',
                'headers' => [],
            ],
            default => [],
        };
    }

    public function getDefaultConditions(): array
    {
        return [
            'on_failure' => true,
            'on_recovery' => true,
            'min_failures' => 1,
            'failure_window' => 3600, // 1 hour
            'throttle_minutes' => 15,
        ];
    }
}
