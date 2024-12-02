<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $host
 * @property int $port
 * @property string|null $username
 * @property mixed|null $password
 * @property bool $ssl_enabled
 * @property mixed|null $ssl_ca
 * @property mixed|null $ssl_cert
 * @property mixed|null $ssl_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Server> $servers
 * @property-read int|null $servers_count
 * @method static \Database\Factories\MqttBrokerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereSslCa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereSslCert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereSslEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereSslKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MqttBroker whereUsername($value)
 * @mixin \Eloquent
 */
class MqttBroker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'ssl_enabled',
        'ssl_ca',
        'ssl_cert',
        'ssl_key',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'password' => 'encrypted',
        'ssl_ca' => 'encrypted',
        'ssl_cert' => 'encrypted',
        'ssl_key' => 'encrypted',
    ];

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
