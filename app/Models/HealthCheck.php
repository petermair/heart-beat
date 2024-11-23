<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property-read \App\Models\Server|null $server
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck query()
 * @property int $id
 * @property int $server_id
 * @property string $status
 * @property float|null $response_time
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon $checked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthCheck whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'status',
        'response_time',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'response_time' => 'float',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
