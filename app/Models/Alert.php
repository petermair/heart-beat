<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\AlertRule|null $alertRule
 * @property-read \App\Models\Device|null $device
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert query()
 *
 * @property int $id
 * @property int $alert_rule_id
 * @property int $device_id
 * @property string $message
 * @property string $status
 * @property \Illuminate\Support\Carbon $triggered_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereAlertRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereTriggeredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_rule_id',
        'device_id',
        'message',
        'status',
        'triggered_at',
        'resolved_at',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
