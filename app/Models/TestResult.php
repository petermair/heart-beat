<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $device_id
 * @property int $test_scenario_id
 * @property string $test_type
 * @property bool $success
 * @property string|null $error_message
 * @property float|null $response_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @property-read \App\Models\TestScenario $testScenario
 * @property-read float $success_rate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereTestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'test_scenario_id',
        'test_type',
        'success',
        'error_message',
        'response_time',
    ];

    protected $casts = [
        'success' => 'boolean',
        'response_time' => 'float',
    ];

    protected $appends = [
        'success_rate'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->success ? 100.0 : 0.0;
    }
}
