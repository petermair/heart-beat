<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMonitoringResult extends Model
{
    protected $fillable = [
        'device_id',
        'chirpstack_status',
        'thingsboard_status',
        'chirpstack_response_time',
        'thingsboard_response_time',
        'success',
        'error_message',
        'test_type',
        'additional_data',
    ];

    protected $casts = [
        'chirpstack_status' => 'boolean',
        'thingsboard_status' => 'boolean',
        'chirpstack_response_time' => 'integer',
        'thingsboard_response_time' => 'integer',
        'success' => 'boolean',
        'additional_data' => 'json',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
