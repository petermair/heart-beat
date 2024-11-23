<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $interface_class
 * @property string|null $description
 * @property array|null $required_settings
 * @property string|null $monitoring_interface
 * @property array|null $required_credentials
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Server> $servers
 * @property-read int|null $servers_count
 * @method static \Database\Factories\ServerTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereInterfaceClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereMonitoringInterface($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereRequiredCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereRequiredSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServerType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interface_class',
        'description',
        'required_settings',
        'required_credentials',
    ];

    protected $casts = [
        'required_settings' => 'array',
        'required_credentials' => 'array',
    ];

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getMonitoringInterface()
    {
        if (class_exists($this->interface_class)) {
            return new $this->interface_class();
        }
        throw new \RuntimeException("Monitoring interface class {$this->interface_class} not found");
    }
}
