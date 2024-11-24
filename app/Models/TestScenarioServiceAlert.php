<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property string $service_type
 * @property string $alert_type
 * @property string $status
 * @property string $message
 * @property \Illuminate\Support\Carbon $triggered_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $acknowledgedBy
 * @property-read \App\Models\TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAcknowledgedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAlertType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereTriggeredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestScenarioServiceAlert extends Model
{
    protected $fillable = [
        'test_scenario_id',
        'service_type',
        'alert_type',
        'status',
        'message',
        'triggered_at',
        'resolved_at',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    // Alert type constants
    public const ALERT_CRITICAL = 'CRITICAL';
    public const ALERT_WARNING = 'WARNING';

    // Status constants
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_RESOLVED = 'RESOLVED';

    // Service type constants (using TestResult constants)
    public const SERVICE_THINGSBOARD = TestResult::SERVICE_THINGSBOARD;
    public const SERVICE_CHIRPSTACK = TestResult::SERVICE_CHIRPSTACK;
    public const SERVICE_MQTT = TestResult::SERVICE_MQTT;
    public const SERVICE_LORATX = TestResult::SERVICE_LORATX;
    public const SERVICE_LORARX = TestResult::SERVICE_LORARX;

    // Relationships
    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Helper methods
    public function isCritical(): bool
    {
        return $this->alert_type === self::ALERT_CRITICAL;
    }

    public function isWarning(): bool
    {
        return $this->alert_type === self::ALERT_WARNING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function getAlertTypeList(): array
    {
        return [
            self::ALERT_CRITICAL => 'Critical',
            self::ALERT_WARNING => 'Warning',
        ];
    }

    public function getStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    public function getServiceTypeList(): array
    {
        return (new TestResult())->getServiceTypeList();
    }

    public function acknowledge(User $user): void
    {
        if (!$this->isAcknowledged()) {
            $this->update([
                'acknowledged_at' => now(),
                'acknowledged_by' => $user->id,
            ]);
        }
    }

    public function resolve(): void
    {
        if ($this->isActive()) {
            $this->update([
                'status' => self::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]);
        }
    }
}
