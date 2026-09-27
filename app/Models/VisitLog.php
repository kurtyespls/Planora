<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A check-in / check-out pair recorded by a traveler at a point of interest.
 *
 * `location_id` is nullable because nearby places come from the OpenStreetMap
 * Overpass lookup, which returns many spots that are not in the curated
 * `locations` table. In that case the free-text `location_name` is kept so the
 * visit is still recorded.
 */
class VisitLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'location_name',
        'checked_in_at',
        'checked_out_at',
        'duration_minutes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('checked_out_at');
    }

    public function scopeAtPlace(Builder $query, string $name): Builder
    {
        return $query->whereRaw('LOWER(location_name) = ?', [strtolower(trim($name))]);
    }
}
