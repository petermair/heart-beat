<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MqttBroker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'ssl_enabled',
        'description',
        'monitoring_interval',
        'is_active',
        'credentials', // JSON field for username, password, certificates
        'test_topic',  // Topic to use for availability testing
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ssl_enabled' => 'boolean',
        'credentials' => 'encrypted:array',
        'monitoring_interval' => 'integer',
    ];

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }
}
