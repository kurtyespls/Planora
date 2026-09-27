<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A point of interest in Dagupan City that is not a hotel.
 *
 * Categories mirror the keys returned by PlanoraService::categorizePlaces()
 * so the same vocabulary is used for Overpass API results and seeded data.
 */
class Location extends Model
{
    public const CATEGORY_RESTAURANT = 'restaurant';
    public const CATEGORY_MALL = 'mall';
    public const CATEGORY_BEACH = 'beach';
    public const CATEGORY_TOURIST = 'tourist';

    /**
     * The attributes that are mass assignable.
     * Note: budgetPerDay keeps the camelCase column name of the original table.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'latitude',
        'longitude',
        'rating',
        'category',
        'icon',
        'budgetPerDay',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'rating' => 'float',
        'budgetPerDay' => 'float',
    ];

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeRestaurants(Builder $query): Builder
    {
        return $query->ofCategory(self::CATEGORY_RESTAURANT);
    }

    public function scopeBeaches(Builder $query): Builder
    {
        return $query->ofCategory(self::CATEGORY_BEACH);
    }

    public function scopeTouristSpots(Builder $query): Builder
    {
        return $query->ofCategory(self::CATEGORY_TOURIST);
    }

    public static function distanceSql(float $latitude, float $longitude): string
    {
        return sprintf(
            '(6371 * ACOS(LEAST(1, COS(RADIANS(%1$F)) * COS(RADIANS(latitude))'
            . ' * COS(RADIANS(longitude) - RADIANS(%2$F)) + SIN(RADIANS(%1$F)) * SIN(RADIANS(latitude)))))',
            $latitude,
            $longitude
        );
    }
}
