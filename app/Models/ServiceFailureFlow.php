<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pattern_id
 * @property int $flow_number
 * @property bool $fails
 * @property bool $is_optional
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ServiceFailurePattern $pattern
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereFails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereFlowNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereIsOptional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow wherePatternId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFailureFlow whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceFailureFlow extends Model
{
    protected $fillable = [
        'pattern_id',
        'flow_number',
        'fails',
        'is_optional',
    ];

    protected $casts = [
        'fails' => 'boolean',
        'is_optional' => 'boolean',
    ];

    public function pattern()
    {
        return $this->belongsTo(ServiceFailurePattern::class, 'pattern_id');
    }
}
