<?php

namespace App\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $notification_type_id
 * @property string $configuration
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereNotificationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSettings whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NotificationSettings extends Model
{
    public array $sms_numbers;

    public array $email_addresses;

    public array $telegram_chats;

    public array $slack_webhooks;

    public array $default_channels;

    public bool $notifications_enabled;

    public int $notification_cooldown; // in minutes

    public array $notification_schedules; // time windows when notifications are allowed

    public array $severity_levels; // mapping of severity levels to notification channels

    public static function group(): string
    {
        return 'notifications';
    }

    public function getDefaultChannels(): array
    {
        return $this->default_channels ?? ['sms', 'email'];
    }

    public function isNotificationAllowed(): bool
    {
        return $this->notifications_enabled ?? true;
    }
}
