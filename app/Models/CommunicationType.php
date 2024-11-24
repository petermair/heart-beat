<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Device> $devices
 * @property-read int|null $devices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunicationType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CommunicationType extends Model
{
    protected $fillable = [
        'name',
        'label',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
