<?php

namespace App\Models;

use App\Enums\FlowType;
use App\Enums\TestResultStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageFlow extends Model
{
    protected $fillable = [
        'test_result_id',
        'flow_type',
        'flow_number',
        'description',
        'status',
        'response_time_ms',
        'started_at',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'flow_type' => FlowType::class,
        'flow_number' => 'integer',
        'status' => TestResultStatus::class,
        'response_time_ms' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isSuccessful(): bool
    {
        return $this->status === TestResultStatus::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === TestResultStatus::FAILURE;
    }
}
