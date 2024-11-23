<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'name',
        'description',
        'thingsboard_server_id',
        'chirpstack_server_id',
        'application_id',
        'device_profile_id',
        'device_eui',
        'communication_type_id',
        'is_active',
        'last_seen_at',
        'monitoring_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function thingsboardServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'thingsboard_server_id');
    }

    public function chirpstackServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'chirpstack_server_id');
    }

    public function communicationType(): BelongsTo
    {
        return $this->belongsTo(CommunicationType::class);
    }

    public function monitoringResults(): HasMany
    {
        return $this->hasMany(DeviceMonitoringResult::class);
    }

    public function latestMonitoringResult()
    {
        return $this->monitoringResults()->latest()->first();
    }

    public function getSuccessRate(): float
    {
        $total = $this->monitoringResults()->count();
        if ($total === 0) {
            return 0;
        }

        $successful = $this->monitoringResults()->where('success', true)->count();
        return round(($successful / $total) * 100, 2);
    }

    public function getAverageResponseTime(): ?float
    {
        $results = $this->monitoringResults()
            ->whereNotNull('chirpstack_response_time')
            ->whereNotNull('thingsboard_response_time')
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        $avgChirpstack = $results->avg('chirpstack_response_time');
        $avgThingsboard = $results->avg('thingsboard_response_time');

        return round(($avgChirpstack + $avgThingsboard) / 2, 2);
    }
}
