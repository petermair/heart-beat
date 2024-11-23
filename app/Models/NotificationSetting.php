<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
