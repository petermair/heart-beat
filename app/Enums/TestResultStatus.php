<?php

namespace App\Enums;

/**
 * Test Result Status Enum
 * 
 * IMPORTANT: These are the only valid states for test results.
 * DO NOT add or modify these states without careful consideration
 * as they are used across the system for flow validation.
 * 
 * Current states:
 * - PENDING: Initial state when test starts
 * - SUCCESS: Test completed successfully
 * - FAILURE: Test failed with an error
 * - TIMEOUT: Test didn't complete within expected time
 */
enum TestResultStatus: string
{
    case PENDING = 'PENDING';   // Initial state when test starts
    case SUCCESS = 'SUCCESS';   // Test completed successfully
    case FAILURE = 'FAILURE';   // Test failed with an error
    case TIMEOUT = 'TIMEOUT';   // Test didn't complete within expected time

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Success',
            self::FAILURE => 'Failure',
            self::TIMEOUT => 'Timeout',
        };
    }
}
