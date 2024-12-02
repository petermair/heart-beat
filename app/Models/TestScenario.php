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
 * @property string|null $mqtt_last_success_at
 * @property float $mqtt_success_rate_1h
 * @property float $mqtt_success_rate_24h
 * @property int $mqtt_messages_count_1h
 * @property int $mqtt_messages_count_24h
 * @property string $mqtt_status
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
 * @property-read \App\Models\TestResult|null $latestResult
 * @property-read \App\Models\Device|null $mqttDevice
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttLastSuccessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttMessagesCount1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttMessagesCount24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttSuccessRate1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestScenario whereMqttSuccessRate24h($value)
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
        'test_type',
        'device_id',
        'mqtt_device_id',
        'http_device_id',
        'is_active',
        'timeout_seconds',
        'notification_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'timeout_seconds' => 'integer',
        'notification_settings' => 'array',
    ];

    // Relationships
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

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

    public function serviceStatuses(): HasMany
    {
        return $this->hasMany(TestScenarioServiceStatus::class);
    }
}
