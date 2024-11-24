<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $device_id
 * @property bool $chirpstack_status
 * @property bool $thingsboard_status
 * @property int|null $chirpstack_response_time
 * @property int|null $thingsboard_response_time
 * @property bool $success
 * @property string|null $error_message
 * @property string $test_type
 * @property array|null $additional_data
 * @property int|null $response_time_ms
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @property-read \App\Models\TestScenario $testScenario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereAdditionalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereChirpstackResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereChirpstackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereResponseTimeMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereTestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereThingsboardResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereThingsboardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereUpdatedAt($value)
 * @property int|null $test_scenario_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceMonitoringResult whereTestScenarioId($value)
 * @mixin \Eloquent
 */
class DeviceMonitoringResult extends Model
{
    protected $fillable = [
        'device_id',
        'chirpstack_status',
        'thingsboard_status',
        'chirpstack_response_time',
        'thingsboard_response_time',
        'success',
        'error_message',
        'test_type',
        'additional_data',
        'response_time_ms',
        'metadata'
    ];

    protected $casts = [
        'chirpstack_status' => 'boolean',
        'thingsboard_status' => 'boolean',
        'success' => 'boolean',
        'additional_data' => 'array',
        'metadata' => 'array'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }
}
