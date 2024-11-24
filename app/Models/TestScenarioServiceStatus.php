<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property string $service_type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_success_at
 * @property string|null $last_failure_at
 * @property int $success_count_1h
 * @property int $total_count_1h
 * @property float $success_rate_1h
 * @property string|null $downtime_started_at
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
    public const STATUS_HEALTHY = 'HEALTHY';
    public const STATUS_WARNING = 'WARNING';
    public const STATUS_CRITICAL = 'CRITICAL';

    protected $fillable = [
        'test_scenario_id',
        'service_type',
        'status',
        'success_count_1h',
        'total_count_1h',
        'success_rate_1h',
        'last_success',
        'last_check_at',
        'last_success_at',
    ];

    protected $casts = [
        'success_count_1h' => 'integer',
        'total_count_1h' => 'integer',
        'success_rate_1h' => 'float',
        'last_success' => 'boolean',
        'last_check_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    public function updateStatus(bool $success): void
    {
        // Update counters
        $this->updateCounters($success);

        // Calculate success rate
        $this->calculateSuccessRate();

        // Determine status based on success rate
        $this->determineStatus();

        // Update timestamps
        $this->last_check_at = now();
        if ($success) {
            $this->last_success_at = now();
        }
        $this->last_success = $success;

        $this->save();
    }

    protected function updateCounters(bool $success): void
    {
        // Get results from the last hour
        $oneHourAgo = Carbon::now()->subHour();

        // Reset counters if no recent checks
        if ($this->last_check_at === null || $this->last_check_at->lt($oneHourAgo)) {
            $this->success_count_1h = $success ? 1 : 0;
            $this->total_count_1h = 1;
            return;
        }

        // Update counters
        $this->total_count_1h++;
        if ($success) {
            $this->success_count_1h++;
        }
    }

    protected function calculateSuccessRate(): void
    {
        $this->success_rate_1h = $this->total_count_1h > 0
            ? ($this->success_count_1h / $this->total_count_1h) * 100
            : 100;
    }

    protected function determineStatus(): void
    {
        $this->status = match (true) {
            $this->success_rate_1h >= 90 => self::STATUS_HEALTHY,
            $this->success_rate_1h >= 75 => self::STATUS_WARNING,
            default => self::STATUS_CRITICAL,
        };
    }
}
