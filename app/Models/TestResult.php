<?php

namespace App\Models;

use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Enums\TestResultStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property int $device_id
 * @property FlowType $flow_type
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property TestResultStatus $status
 * @property string|null $error_message
 * @property float|null $execution_time_ms
 * @property ServiceType $service_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Device|null $device
 * @property-read float $success_rate
 * @property-read Device|null $httpDevice
 * @property-read TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereExecutionTimeMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereFlowType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestResult whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestResult extends Model
{
    protected $fillable = [
        'test_scenario_id',
        'device_id',
        'flow_type',
        'start_time',
        'end_time',
        'status',
        'error_message',
        'execution_time_ms',
        'service_type',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => TestResultStatus::class,
        'flow_type' => FlowType::class,
        'service_type' => ServiceType::class,
        'execution_time_ms' => 'float',
    ];

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === TestResultStatus::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === TestResultStatus::FAILURE;
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->status === TestResultStatus::SUCCESS ? 100.0 : 0.0;
    }

    public function getStatusList(): array
    {
        return collect(TestResultStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function getFlowTypeList(): array
    {
        return collect(FlowType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function getServiceTypeList(): array
    {
        return collect(ServiceType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function messageFlows()
{
    return $this->hasMany(MessageFlow::class);
}

public function deviceMessages()
{
    return $this->hasMany(DeviceMessage::class);
}


}
