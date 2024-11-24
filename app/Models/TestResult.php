<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TestScenario;

class TestResult extends Model
{
    protected $fillable = [
        'test_scenario_id',
        'status',
        'response_time_ms',
        'error_message',
        'request_data',
        'response_data',
        'metadata',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'metadata' => 'array',
        'response_time_ms' => 'integer',
    ];

    // Status constants
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILURE = 'failure';
    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_ERROR = 'error';

    // Relationships
    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    // Helper methods
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this->status === self::STATUS_FAILURE;
    }

    public function isTimeout(): bool
    {
        return $this->status === self::STATUS_TIMEOUT;
    }

    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function getStatusList(): array
    {
        return [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILURE => 'Failure',
            self::STATUS_TIMEOUT => 'Timeout',
            self::STATUS_ERROR => 'Error',
        ];
    }

    protected static function booted()
    {
        static::created(function ($testResult) {
            // Update the test scenario's success rates when a new result is created
            $testResult->testScenario->updateSuccessRate($testResult->isSuccess());
        });
    }
}
