<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
