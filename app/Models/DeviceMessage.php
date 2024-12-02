<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'message_flow_id',
        'source',
        'success',
        'error_message',
        'response_time_ms',
        'metadata',
    ];

    protected $casts = [
        'success' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function messageFlow(): BelongsTo
    {
        return $this->belongsTo(MessageFlow::class);
    }
}
