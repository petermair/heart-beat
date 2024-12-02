<?php

namespace App\Enums;

/**
 * Flow Type Enum
 * 
 * IMPORTANT: These are the only valid flow types for message validation.
 * DO NOT add or modify these types without careful consideration
 * as they are used across the system for flow validation.
 * 
 * Flow Types:
 * 1. TB_TO_CS: Route from ThingsBoard to ChirpStack
 * 2. CS_TO_TB: Route from ChirpStack to ThingsBoard
 * 3. CS_TO_TB_TO_CS: Complete round trip (TB → CS → TB)
 * 4. DIRECT_TEST_CS_TB: Direct test ChirpStack to ThingsBoard
 * 5. DIRECT_TEST_TB_CS: Direct test ThingsBoard to ChirpStack
 * 6. TB_MQTT_HEALTH: ThingsBoard MQTT connection health
 * 7. CS_MQTT_HEALTH: ChirpStack MQTT connection health
 * 8. TB_HTTP_HEALTH: ThingsBoard HTTP connection health
 * 9. CS_HTTP_HEALTH: ChirpStack HTTP connection health
 */
enum FlowType: string
{
    case TB_TO_CS = 'TB_TO_CS';                     // 1. Route TB to CS
    case CS_TO_TB = 'CS_TO_TB';                     // 2. Route CS to TB
    case CS_TO_TB_TO_CS = 'CS_TO_TB_TO_CS';         // 3. Route TB-CS-TB
    case DIRECT_TEST_CS_TB = 'DIRECT_TEST_CS_TB';   // 4. Direct Test CS to TB
    case DIRECT_TEST_TB_CS = 'DIRECT_TEST_TB_CS';   // 5. Direct Test TB to CS
    case TB_MQTT_HEALTH = 'TB_MQTT_HEALTH';         // 6. TB MQTT Health
    case CS_MQTT_HEALTH = 'CS_MQTT_HEALTH';         // 7. CS MQTT Health
    case TB_HTTP_HEALTH = 'TB_HTTP_HEALTH';         // 8. TB HTTP Health
    case CS_HTTP_HEALTH = 'CS_HTTP_HEALTH';         // 9. CS HTTP Health

    public function label(): string
    {
        return match($this) {
            self::TB_TO_CS => 'Route: ThingsBoard → ChirpStack',
            self::CS_TO_TB => 'Route: ChirpStack → ThingsBoard',
            self::CS_TO_TB_TO_CS => 'Route: TB → CS → TB',
            self::DIRECT_TEST_CS_TB => 'Direct Test: CS → TB',
            self::DIRECT_TEST_TB_CS => 'Direct Test: TB → CS',
            self::TB_MQTT_HEALTH => 'ThingsBoard MQTT Health',
            self::CS_MQTT_HEALTH => 'ChirpStack MQTT Health',
            self::TB_HTTP_HEALTH => 'ThingsBoard HTTP Health',
            self::CS_HTTP_HEALTH => 'ChirpStack HTTP Health',
        };
    }
}
