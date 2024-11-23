<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'server_type_id',
        'url',
        'description',
        'monitoring_interval',
        'is_active',
        'credentials',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'settings' => 'encrypted:array',
        'monitoring_interval' => 'integer',
    ];

    public function serverType(): BelongsTo
    {
        return $this->belongsTo(ServerType::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function getMonitor()
    {
        return $this->serverType->getMonitoringInterface();
    }

    public function validateSettings(): bool
    {
        $requiredSettings = $this->serverType->required_settings ?? [];
        $currentSettings = $this->settings ?? [];
        
        return empty(array_diff($requiredSettings, array_keys($currentSettings)));
    }

    public function validateCredentials(): bool
    {
        $requiredCredentials = $this->serverType->required_credentials ?? [];
        $currentCredentials = $this->credentials ?? [];
        
        return empty(array_diff($requiredCredentials, array_keys($currentCredentials)));
    }
}
