<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int|null $server_id
 * @property int|null $mqtt_broker_id
 * @property array $conditions
 * @property array $actions
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Alert> $alerts
 * @property-read int|null $alerts_count
 * @property-read \App\Models\MqttBroker|null $mqttBroker
 * @property-read \App\Models\Server|null $server
 * @method static \Database\Factories\AlertRuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereActions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereMqttBrokerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertRule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'server_id',
        'mqtt_broker_id',
        'conditions',
        'actions',
        'description',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function mqttBroker(): BelongsTo
    {
        return $this->belongsTo(MqttBroker::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
