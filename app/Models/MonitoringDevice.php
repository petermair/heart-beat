<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoringDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoringDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoringDevice query()
 * @mixin \Eloquent
 */
class MonitoringDevice extends Model
{
    protected $fillable = [
        'name',
        'type',
        'settings',
        'credentials',
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'array',
    ];
}
