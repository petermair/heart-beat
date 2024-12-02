<?php

namespace App\Enums;

/**
 * Alert Type Enum
 * 
 * IMPORTANT: These are the only valid alert types for system monitoring.
 * DO NOT add or modify these types without careful consideration
 * as they are used for alerting and notification across the system.
 * 
 * Current types:
 * - CRITICAL: Service down for more than 10 minutes
 * - WARNING: Service down >10% in last hour
 */
enum AlertType: string
{
    case CRITICAL = 'CRITICAL'; // Service down for more than 10 minutes
    case WARNING = 'WARNING';   // Service down >10% in last hour

    public function label(): string
    {
        return match($this) {
            self::CRITICAL => 'Critical - Service Down',
            self::WARNING => 'Warning - Service Degraded',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CRITICAL => 'danger',
            self::WARNING => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CRITICAL => 'heroicon-o-exclamation-circle',
            self::WARNING => 'heroicon-o-exclamation-triangle',
        };
    }
}
