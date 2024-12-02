<?php

namespace App\Enums;

/**
 * Status Type Enum
 * 
 * IMPORTANT: These are the only valid status types for service health.
 * DO NOT add or modify these types without careful consideration
 * as they are used for service health monitoring across the system.
 * 
 * Current types:
 * - HEALTHY: Service is up and running normally
 * - WARNING: Service is experiencing issues but still operational
 * - CRITICAL: Service is down or not responding
 */
enum StatusType: string
{
    case HEALTHY = 'HEALTHY';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';

    public function label(): string
    {
        return match($this) {
            self::HEALTHY => 'Healthy',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::HEALTHY => 'success',
            self::WARNING => 'warning',
            self::CRITICAL => 'danger',
        };
    }
}
