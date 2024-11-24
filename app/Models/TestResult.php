<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\TestScenario;
use App\Models\Device;

/**
 * 
 *
 * @property int $id
 * @property int $test_scenario_id
 * @property int $device_id
 * @property string $flow_type
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $status
 * @property string|null $error_message
 * @property float|null $execution_time_ms
 * @property string|null $service_type
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
    // Statuses
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_FAILURE = 'FAILURE';

    // Flow Types
    public const FLOW_FULL_ROUTE_1 = 'FULL_ROUTE_1';
    public const FLOW_ONE_WAY_ROUTE = 'ONE_WAY_ROUTE';
    public const FLOW_TWO_WAY_ROUTE = 'TWO_WAY_ROUTE';
    public const FLOW_DIRECT_TEST_1 = 'DIRECT_TEST_1';
    public const FLOW_DIRECT_TEST_2 = 'DIRECT_TEST_2';
    public const FLOW_TB_MQTT_HEALTH = 'TB_MQTT_HEALTH';
    public const FLOW_CS_MQTT_HEALTH = 'CS_MQTT_HEALTH';
    public const FLOW_TB_HTTP_HEALTH = 'TB_HTTP_HEALTH';
    public const FLOW_CS_HTTP_HEALTH = 'CS_HTTP_HEALTH';

    // Service Types
    public const SERVICE_THINGSBOARD = 'ThingsBoard';
    public const SERVICE_CHIRPSTACK = 'ChirpStack';
    public const SERVICE_MQTT = 'MQTT';
    public const SERVICE_LORATX = 'LoRa TX';
    public const SERVICE_LORARX = 'LoRa RX';
    public const SERVICE_UNKNOWN = 'Unknown';

    protected $fillable = [
        'test_scenario_id',
        'flow_type',
        'status',
        'error_message',
        'start_time',
        'end_time',
        'execution_time_ms',
        'service_type',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'execution_time_ms' => 'float',
    ];

    public function testScenario(): BelongsTo
    {
        return $this->belongsTo(TestScenario::class);
    }

    public function device()
    {
        // Test results are always associated with the MQTT device
        return $this->hasOneThrough(
            Device::class,
            TestScenario::class,
            'id', // Foreign key on test_scenarios table...
            'id', // Foreign key on devices table...
            'test_scenario_id', // Local key on test_results table...
            'mqtt_device_id' // Local key on test_scenarios table...
        );
    }

    public function httpDevice()
    {
        // For HTTP tests, we can also access the HTTP device
        return $this->hasOneThrough(
            Device::class,
            TestScenario::class,
            'id', // Foreign key on test_scenarios table...
            'id', // Foreign key on devices table...
            'test_scenario_id', // Local key on test_results table...
            'http_device_id' // Local key on test_scenarios table...
        );
    }

    // Helper methods
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this->status === self::STATUS_FAILURE;
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->status === self::STATUS_SUCCESS ? 100.0 : 0.0;
    }

    public function getStatusList(): array
    {
        return [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILURE => 'Failure',
        ];
    }

    public function getFlowTypeList(): array
    {
        return [
            self::FLOW_FULL_ROUTE_1 => 'Full Route 1 (TB → CS)',
            self::FLOW_ONE_WAY_ROUTE => 'One-way Route (CS → TB)',
            self::FLOW_TWO_WAY_ROUTE => 'Two-way Route (CS → TB → CS)',
            self::FLOW_DIRECT_TEST_1 => 'Direct Test 1 (CS → TB)',
            self::FLOW_DIRECT_TEST_2 => 'Direct Test 2 (TB → CS)',
            self::FLOW_TB_MQTT_HEALTH => 'ThingsBoard MQTT Health',
            self::FLOW_CS_MQTT_HEALTH => 'ChirpStack MQTT Health',
            self::FLOW_TB_HTTP_HEALTH => 'ThingsBoard HTTP Health',
            self::FLOW_CS_HTTP_HEALTH => 'ChirpStack HTTP Health',
        ];
    }

    public function getServiceTypeList(): array
    {
        return [
            self::SERVICE_THINGSBOARD => 'ThingsBoard',
            self::SERVICE_CHIRPSTACK => 'ChirpStack',
            self::SERVICE_MQTT => 'MQTT Broker',
            self::SERVICE_LORATX => 'LoRa TX',
            self::SERVICE_LORARX => 'LoRa RX',
            self::SERVICE_UNKNOWN => 'Unknown',
        ];
    }

    protected static function booted()
    {
        static::created(function ($testResult) {
            // Update the test scenario's service status when a new result is created
            if ($testResult->service_type) {
                $testResult->testScenario->updateServiceStatus(
                    $testResult->service_type,
                    $testResult->isSuccess(),
                    $testResult->execution_time_ms
                );
            }
        });
    }
}
