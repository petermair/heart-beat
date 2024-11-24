<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceFailureFlow;

/**
 * 
 *
 * @property int $id
 * @property string $service_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ServiceFailureFlow> $flows
 * @property-read int|null $flows_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailurePattern whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceFailurePattern extends Model
{
    protected $fillable = [
        'service_name',
        'description'
    ];

    public function flows()
    {
        return $this->hasMany(ServiceFailureFlow::class, 'pattern_id');
    }

    /**
     * Check if a given set of failed flows matches this pattern
     * @param array $failedFlowNumbers Array of flow numbers that failed
     * @param bool $hasHttpDevice Whether an HTTP device is present
     */
    public function matchesFailedFlows(array $failedFlowNumbers, bool $hasHttpDevice = false): bool
    {
        // Get mandatory flows that should fail according to this pattern
        $shouldFailFlows = $this->flows()
            ->where('fails', true)
            ->where('is_optional', false)
            ->pluck('flow_number')
            ->toArray();

        // Get mandatory flows that should NOT fail according to this pattern
        $shouldPassFlows = $this->flows()
            ->where('fails', false)
            ->where('is_optional', false)
            ->pluck('flow_number')
            ->toArray();

        // If HTTP device is present, include optional flows in the check
        if ($hasHttpDevice) {
            $optionalFailFlows = $this->flows()
                ->where('fails', true)
                ->where('is_optional', true)
                ->pluck('flow_number')
                ->toArray();
            
            $optionalPassFlows = $this->flows()
                ->where('fails', false)
                ->where('is_optional', true)
                ->pluck('flow_number')
                ->toArray();

            $shouldFailFlows = array_merge($shouldFailFlows, $optionalFailFlows);
            $shouldPassFlows = array_merge($shouldPassFlows, $optionalPassFlows);
        }

        // For an exact match:
        // 1. All required flows (and optional flows if HTTP device) that should fail must be in the failed flows
        // 2. None of the flows that should pass should be in the failed flows
        $matchingFailures = array_intersect($shouldFailFlows, $failedFlowNumbers);
        $allRequiredFailuresMatch = count($matchingFailures) === count($shouldFailFlows);
        
        $noConflictingPasses = empty(array_intersect($shouldPassFlows, $failedFlowNumbers));

        return $allRequiredFailuresMatch && $noConflictingPasses;
    }
}
