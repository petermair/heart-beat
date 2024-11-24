<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
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
