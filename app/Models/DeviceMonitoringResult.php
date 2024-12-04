<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMonitoringResult extends Model
{
    protected $fillable = [
        'device_id',
        'status',
        'response_time',
        'error_message',
        'details',
    ];

    protected $casts = [
        'response_time' => 'float',
        'details' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
