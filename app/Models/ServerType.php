<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interface_class',
        'description',
        'required_settings',
        'required_credentials',
    ];

    protected $casts = [
        'required_settings' => 'array',
        'required_credentials' => 'array',
    ];

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getMonitoringInterface()
    {
        if (class_exists($this->interface_class)) {
            return new $this->interface_class();
        }
        throw new \RuntimeException("Monitoring interface class {$this->interface_class} not found");
    }
}
