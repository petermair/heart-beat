<?php

namespace App\Enums;

/**
 * Service Type Enum
 * 
 * IMPORTANT: These are the only valid service types in the system.
 * DO NOT add or modify these types without careful consideration
 * as they are used for service identification and error tracking.
 * 
 * Current types:
 * - THINGSBOARD: ThingsBoard IoT platform service
 * - CHIRPSTACK: ChirpStack LoRaWAN network server
 */
enum ServiceType: string
{
    case CHIRPSTACK = 'chirpstack';
    case THINGSBOARD = 'thingsboard';
    case MQTT_TB = 'mqtt_tb';
    case MQTT_CS = 'mqtt_cs';
    case LORATX = 'loratx';
    case LORARX = 'lorarx';

    public function label(): string
    {
        return match($this) {
            self::CHIRPSTACK => 'ChirpStack',
            self::THINGSBOARD => 'ThingsBoard',
            self::MQTT_TB => 'MQTT TB',
            self::MQTT_CS => 'MQTT CS',
            self::LORATX => 'LoRa TX',
            self::LORARX => 'LoRa RX',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
