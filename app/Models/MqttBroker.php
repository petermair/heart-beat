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
        'username',
        'password',
        'ssl_enabled',
        'ssl_ca',
        'ssl_cert',
        'ssl_key',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'password' => 'encrypted',
        'ssl_ca' => 'encrypted',
        'ssl_cert' => 'encrypted',
        'ssl_key' => 'encrypted',
    ];

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
