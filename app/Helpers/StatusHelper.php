<?php

namespace App\Helpers;

use App\Enums\StatusType;

class StatusHelper
{
    public static function getStatusEmoji(string $status): string
    {
        return match ($status) {
            StatusType::HEALTHY->value => '✅',
            StatusType::WARNING->value => '⚠️',
            StatusType::CRITICAL->value => '❌',
            default => '❓',
        };
    }

    public static function getStatusDescription(array $services): string
    {
        foreach ($services as $name => $status) {
            if ($status === StatusType::CRITICAL->value) {
                return self::getStatusEmoji($status) . " $status - $name has critical issues";
            }
            if ($status === StatusType::WARNING->value) {
                return self::getStatusEmoji($status) . " $status - $name warning detected";
            }
        }
        
        return self::getStatusEmoji(StatusType::HEALTHY->value) . ' All services are healthy';
    }

    public static function formatStatus(string $status): string
    {
        return self::getStatusEmoji($status) . ' ' . $status;
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            StatusType::HEALTHY->value => 'green',
            StatusType::WARNING->value => 'yellow',
            StatusType::CRITICAL->value => 'red',
            default => 'gray',
        };
    }

    public static function getStatusBold(string $status): bool
    {
        return $status === StatusType::WARNING->value || $status === StatusType::CRITICAL->value;
    }
}
