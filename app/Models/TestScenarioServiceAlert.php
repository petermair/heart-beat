<?php

namespace App\Models;

use App\Enums\ServiceType;
use App\Enums\AlertType;
use App\Enums\AlertStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property ServiceType $service_type
 * @property AlertType $alert_type
 * @property AlertStatus $status
 * @property string $message
 * @property string $metadata
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property-read \App\Models\TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAlertType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereTestScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereUpdatedAt($value)
 * @property string $triggered_at
 * @property int|null $acknowledged_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert acknowledged()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert resolved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereAcknowledgedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenarioServiceAlert whereTriggeredAt($value)
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
        'metadata',
        'started_at',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'alert_type' => AlertType::class,
        'status' => AlertStatus::class,
        'metadata' => 'array',
        'started_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    // Helper methods
    public function acknowledge(): void
    {
        $this->update([
            'status' => AlertStatus::ACKNOWLEDGED,
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => AlertStatus::RESOLVED,
            'resolved_at' => now(),
        ]);
    }

    public function expire(): void
    {
        $this->update([
            'status' => AlertStatus::EXPIRED,
        ]);
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('status', AlertStatus::ACTIVE);
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', AlertStatus::ACKNOWLEDGED);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', AlertStatus::RESOLVED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', AlertStatus::EXPIRED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [AlertStatus::ACTIVE, AlertStatus::ACKNOWLEDGED]);
    }

    // Alert handling methods
    public static function canCreateNewAlert(int $test_scenario_id): bool
    {
        // Check if ALL alerts are RESOLVED
        $allResolved = !self::where('test_scenario_id', $test_scenario_id)
            ->whereNotIn('status', [AlertStatus::RESOLVED])
            ->exists();

        // OR if the last alert is EXPIRED and older than 3 days
        $hasExpiredOldAlert = self::where('test_scenario_id', $test_scenario_id)
            ->where('status', AlertStatus::EXPIRED)
            ->where('updated_at', '<=', now()->subDays(3))
            ->exists();

        return $allResolved || $hasExpiredOldAlert;
    }

    public static function checkAndCreateAlert(
        TestScenario $scenario,
        AlertType $alertType,
        string $message,
        array $metadata = []
    ): ?self {
        if (!self::canCreateNewAlert($scenario->id)) {
            return null;
        }

        return self::create([
            'test_scenario_id' => $scenario->id,
            'service_type' => $scenario->service_type,
            'alert_type' => $alertType,
            'status' => AlertStatus::ACTIVE,
            'message' => $message,
            'metadata' => $metadata,
            'started_at' => now(),
        ]);
    }

    public function shouldBeResolved(TestScenario $scenario): bool
    {
        // Only resolve if system has been healthy for 10 minutes
        return $scenario->isHealthyForMinutes(10);
    }

    public function checkAndUpdateStatus(TestScenario $scenario): void
    {
        if ($this->shouldBeResolved($scenario)) {
            // If alert is older than 3 days, mark as EXPIRED
            if ($this->created_at->diffInDays(now()) > 3) {
                $this->expire();
            } else {
                $this->resolve();
            }
        }
    }
}
