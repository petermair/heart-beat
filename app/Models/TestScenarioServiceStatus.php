<?php

namespace App\Models;

use App\Models\TestScenario;
use App\Enums\ServiceType;
use App\Enums\StatusType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property ServiceType $service_type
 * @property StatusType $status
 * @property int $success_count_1h
 * @property int $total_count_1h
 * @property float $success_rate_1h
 * @property \Illuminate\Support\Carbon|null $last_check_at
 * @property \Illuminate\Support\Carbon|null $last_success_at
 * @property \Illuminate\Support\Carbon|null $last_failure_at
 * @property \Illuminate\Support\Carbon|null $downtime_started_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereDowntimeStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereLastFailureAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereSuccessCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereTotalCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestScenarioServiceStatus extends Model
{
    protected $fillable = [
        'test_scenario_id',
        'service_type',
        'status',
        'success_count_1h',
        'total_count_1h',
        'success_rate_1h',
        'last_check_at',
        'last_success_at',
        'last_failure_at',
        'downtime_started_at',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'status' => StatusType::class,
        'last_check_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'downtime_started_at' => 'datetime',
    ];

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }
}
