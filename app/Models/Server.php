<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $server_type_id
 * @property int|null $mqtt_broker_id
 * @property string|null $url
 * @property string|null $description
 * @property int $monitoring_interval
 * @property bool $is_active
 * @property array|null $credentials
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AlertRule> $alertRules
 * @property-read int|null $alert_rules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HealthCheck> $healthChecks
 * @property-read int|null $health_checks_count
 * @property-read \App\Models\MqttBroker|null $mqttBroker
 * @property-read \App\Models\ServerType $serverType
 *
 * @method static \Database\Factories\ServerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereMonitoringInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereMqttBrokerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereServerTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Server whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'server_type_id',
        'mqtt_broker_id',
        'url',
        'description',
        'monitoring_interval',
        'is_active',
        'credentials',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'settings' => 'encrypted:array',
        'monitoring_interval' => 'integer',
    ];

    public function serverType(): BelongsTo
    {
        return $this->belongsTo(ServerType::class);
    }

    public function mqttBroker(): BelongsTo
    {
        return $this->belongsTo(MqttBroker::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function getMonitor()
    {
        return $this->serverType->getMonitoringInterface();
    }

    public function validateSettings(): bool
    {
        $requiredSettings = $this->serverType->required_settings ?? [];
        $currentSettings = $this->settings ?? [];

        return empty(array_diff($requiredSettings, array_keys($currentSettings)));
    }

    public function validateCredentials(): bool
    {
        $requiredCredentials = $this->serverType->required_credentials ?? [];
        $currentCredentials = $this->credentials ?? [];

        return empty(array_diff($requiredCredentials, array_keys($currentCredentials)));
    }
}
