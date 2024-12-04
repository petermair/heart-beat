<?php

namespace App\Models;

use App\Models\TestScenarioServiceStatus;
use App\Models\Device;
use App\Models\TestResult;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $mqtt_device_id
 * @property int|null $http_device_id
 * @property bool $is_active
 * @property int $timeout_seconds
 * @property string|null $thingsboard_last_success_at
 * @property float $thingsboard_success_rate_1h
 * @property float $thingsboard_success_rate_24h
 * @property int $thingsboard_messages_count_1h
 * @property int $thingsboard_messages_count_24h
 * @property string $thingsboard_status
 * @property string|null $chirpstack_last_success_at
 * @property float $chirpstack_success_rate_1h
 * @property float $chirpstack_success_rate_24h
 * @property int $chirpstack_messages_count_1h
 * @property int $chirpstack_messages_count_24h
 * @property string $chirpstack_status
 * @property string|null $mqtt_tb_last_success_at
 * @property float $mqtt_tb_success_rate_1h
 * @property float $mqtt_tb_success_rate_24h
 * @property int $mqtt_tb_messages_count_1h
 * @property int $mqtt_tb_messages_count_24h
 * @property string $mqtt_tb_status
 * @property string|null $mqtt_cs_last_success_at
 * @property float $mqtt_cs_success_rate_1h
 * @property float $mqtt_cs_success_rate_24h
 * @property int $mqtt_cs_messages_count_1h
 * @property int $mqtt_cs_messages_count_24h
 * @property string $mqtt_cs_status
 * @property string|null $loratx_last_success_at
 * @property float $loratx_success_rate_1h
 * @property float $loratx_success_rate_24h
 * @property int $loratx_messages_count_1h
 * @property int $loratx_messages_count_24h
 * @property string $loratx_status
 * @property string|null $lorarx_last_success_at
 * @property float $lorarx_success_rate_1h
 * @property float $lorarx_success_rate_24h
 * @property int $lorarx_messages_count_1h
 * @property int $lorarx_messages_count_24h
 * @property string $lorarx_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device|null $httpDevice
 * @property-read \App\Models\Device|null $mqttDevice
 * @property-read \App\Models\TestResult|null $latestResult
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationSetting> $notificationSettings
 * @property-read int|null $notification_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationType> $notificationTypes
 * @property-read int|null $notification_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestResult> $results
 * @property-read int|null $results_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestScenarioServiceAlert> $serviceAlerts
 * @property-read int|null $service_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestScenarioServiceStatus> $serviceStatuses
 * @property-read int|null $service_statuses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereChirpstackSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereHttpDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereIntervalSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLorarxSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereLoratxSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMaxRetries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttCsSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttTbSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereThingsboardSuccessRate24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereTimeoutSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestScenario extends Model
{
    protected $fillable = [
        'name',
        'description',
        'mqtt_device_id',
        'http_device_id',
        'is_active',
        'timeout_seconds',
        'thingsboard_last_success_at',
        'thingsboard_success_rate_1h',
        'thingsboard_success_rate_24h',
        'thingsboard_messages_count_1h',
        'thingsboard_messages_count_24h',
        'thingsboard_status',
        'chirpstack_last_success_at',
        'chirpstack_success_rate_1h',
        'chirpstack_success_rate_24h',
        'chirpstack_messages_count_1h',
        'chirpstack_messages_count_24h',
        'chirpstack_status',
        'mqtt_tb_last_success_at',
        'mqtt_tb_success_rate_1h',
        'mqtt_tb_success_rate_24h',
        'mqtt_tb_messages_count_1h',
        'mqtt_tb_messages_count_24h',
        'mqtt_tb_status',
        'mqtt_cs_last_success_at',
        'mqtt_cs_success_rate_1h',
        'mqtt_cs_success_rate_24h',
        'mqtt_cs_messages_count_1h',
        'mqtt_cs_messages_count_24h',
        'mqtt_cs_status',
        'loratx_last_success_at',
        'loratx_success_rate_1h',
        'loratx_success_rate_24h',
        'loratx_messages_count_1h',
        'loratx_messages_count_24h',
        'loratx_status',
        'lorarx_last_success_at',
        'lorarx_success_rate_1h',
        'lorarx_success_rate_24h',
        'lorarx_messages_count_1h',
        'lorarx_messages_count_24h',
        'lorarx_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'timeout_seconds' => 'integer',
        'thingsboard_success_rate_1h' => 'float',
        'thingsboard_success_rate_24h' => 'float',
        'chirpstack_success_rate_1h' => 'float',
        'chirpstack_success_rate_24h' => 'float',
        'mqtt_tb_success_rate_1h' => 'float',
        'mqtt_tb_success_rate_24h' => 'float',
        'mqtt_cs_success_rate_1h' => 'float',
        'mqtt_cs_success_rate_24h' => 'float',
        'loratx_success_rate_1h' => 'float',
        'loratx_success_rate_24h' => 'float',
        'lorarx_success_rate_1h' => 'float',
        'lorarx_success_rate_24h' => 'float',
        'thingsboard_last_success_at' => 'datetime',
        'chirpstack_last_success_at' => 'datetime',
        'mqtt_tb_last_success_at' => 'datetime',
        'mqtt_cs_last_success_at' => 'datetime',
        'loratx_last_success_at' => 'datetime',
        'lorarx_last_success_at' => 'datetime',
    ];

    // Relationships
    public function mqttDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'mqtt_device_id');
    }

    public function httpDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'http_device_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function getStatusColor(string $status): string
    {
        return match (strtoupper($status)) {
            'HEALTHY' => 'success',
            'WARNING' => 'warning',
            'CRITICAL' => 'danger',
            default => 'gray',
        };
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(TestScenarioNotification::class);
    }
}
