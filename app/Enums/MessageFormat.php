<?php

namespace App\Enums;

/**
 * Message Format Enum
 * 
 * IMPORTANT: These are the only valid message formats for device communication.
 * DO NOT add or modify these formats without careful consideration
 * as they are used for payload encoding/decoding across the system.
 * 
 * Current formats:
 * - LPP: Cayenne Low Power Payload format
 * - JSON: JavaScript Object Notation format
 */
enum MessageFormat: string
{
    case JSON = 'json';
    case LPP = 'lpp';
}
