<?php

namespace App\Enums;

/**
 * Alert Status Enum
 * 
 * IMPORTANT: These are the only valid alert statuses for system monitoring.
 * DO NOT add or modify these statuses without careful consideration
 * as they are used for alert lifecycle management across the system.
 * 
 * Current statuses:
 * - ACTIVE: Alert is currently active and unresolved
 * - ACKNOWLEDGED: Alert has been seen but not resolved
 * - RESOLVED: Alert has been resolved
 * - EXPIRED: Alert has expired
 */
enum AlertStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ACKNOWLEDGED = 'ACKNOWLEDGED';
    case RESOLVED = 'RESOLVED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::ACKNOWLEDGED => 'Acknowledged',
            self::RESOLVED => 'Resolved',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'danger',
            self::ACKNOWLEDGED => 'warning',
            self::RESOLVED => 'success',
            self::EXPIRED => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ACTIVE => 'heroicon-o-bell-alert',
            self::ACKNOWLEDGED => 'heroicon-o-bell',
            self::RESOLVED => 'heroicon-o-check-circle',
            self::EXPIRED => 'heroicon-o-clock',
        };
    }
}
