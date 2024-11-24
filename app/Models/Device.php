<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $thingsboard_server_id
 * @property int $chirpstack_server_id
 * @property string $application_id
 * @property string $device_profile_id
 * @property string $device_eui
 * @property int $communication_type_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property bool $monitoring_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Server $chirpstackServer
 * @property-read \App\Models\CommunicationType $communicationType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeviceMonitoringResult> $monitoringResults
 * @property-read int|null $monitoring_results_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestResult> $testResults
 * @property-read int|null $test_results_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestScenario> $testScenarios
 * @property-read int|null $test_scenarios_count
 * @property-read \App\Models\Server $thingsboardServer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereChirpstackServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCommunicationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeviceEui($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeviceProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereMonitoringEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereThingsboardServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUpdatedAt($value)
 *
 * @property string|null $device_type
 * @property array|null $credentials
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeviceType($value)
 *
 * @mixin \Eloquent
 */
class Device extends Model
{
    protected $fillable = [
        'name',
        'description',
        'thingsboard_server_id',
        'chirpstack_server_id',
        'application_id',
        'device_profile_id',
        'device_eui',
        'communication_type_id',
        'is_active',
        'last_seen_at',
        'monitoring_enabled',
        'device_type',
        'credentials',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
        'credentials' => 'encrypted:array',
    ];

    public function thingsboardServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'thingsboard_server_id');
    }

    public function chirpstackServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'chirpstack_server_id');
    }

    public function communicationType(): BelongsTo
    {
        return $this->belongsTo(CommunicationType::class);
    }

    public function monitoringResults(): HasMany
    {
        return $this->hasMany(DeviceMonitoringResult::class);
    }

    public function testScenarios(): HasMany
    {
        return $this->hasMany(TestScenario::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function latestMonitoringResult()
    {
        return $this->monitoringResults()->latest()->first();
    }

    public function getSuccessRate(): float
    {
        $total = $this->monitoringResults()->count();
        if ($total === 0) {
            return 0;
        }

        $successful = $this->monitoringResults()->where('success', true)->count();

        return round(($successful / $total) * 100, 2);
    }

    public function getAverageResponseTime(): ?float
    {
        $results = $this->monitoringResults()
            ->whereNotNull('chirpstack_response_time')
            ->whereNotNull('thingsboard_response_time')
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        $avgChirpstack = $results->avg('chirpstack_response_time');
        $avgThingsboard = $results->avg('thingsboard_response_time');

        return round(($avgChirpstack + $avgThingsboard) / 2, 2);
    }
}
