<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    // Ito ang nagpapahintulot sa mass assignment (para gumana ang $request->all())

    protected $fillable = ['name', 'image_url', 'description', 'rating', 'price', 'lat', 'lon', 'address', 'amenities', 'gallery'];

    /**
     * The attributes that should be cast.
     *
     * `price` is a decimal column and every `rating` lives on the single 0-10
     * scale the admin form validates, so no consumer has to strip currency
     * symbols or guess which rating scale a value uses.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'float',
        'lat' => 'float',
        'lon' => 'float',
    ];

    /**
     * Scope para i-sort ang hotels base sa price.
     * Default: descending (pinakamahal una)
     * Usage: Hotel::orderByPrice()->get() or Hotel::orderByPrice('asc')->get()
     */
    public function scopeOrderByPrice($query, $direction = 'desc')
    {
        return $query->orderBy('price', $direction);
    }
}

