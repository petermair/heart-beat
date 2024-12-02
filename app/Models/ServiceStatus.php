<?php

namespace App\Models;

use App\Enums\ServiceType;
use App\Enums\StatusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStatus extends Model
{
    protected $fillable = [
        'test_scenario_id',
        'service_type',
        'status',
        'last_success_at',
        'last_failure_at',
        'success_count_1h',
        'total_count_1h',
        'success_rate_1h',
        'downtime_started_at',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'status' => StatusType::class,
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'downtime_started_at' => 'datetime',
        'success_rate_1h' => 'float',
    ];

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }
}
