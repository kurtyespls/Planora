<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized caching layer for external API calls.
 * Reduces redundant network requests and improves page load times.
 */
class CacheService
{
    /**
     * Cache TTLs in seconds
     */
    private const TTL = [
        'hotels'      => 300,   // 5 minutes — hotel data rarely changes
        'weather'     => 600,   // 10 minutes — weather updates slowly
        'nearby'      => 86400, // 24 hours — POIs are stable
        'places'      => 86400, // 24 hours — the seeded catalogue only changes on re-seed
        'geocode'     => 604800,// 1 week — coordinates don't change
    ];

    /**
     * Cache the curated place catalogue for one allowance bucket.
     *
     * Two deliberate choices here:
     *
     *  - The key is the *bucketed* allowance, not the raw float. A raw peso
     *    value would mint a cache entry per peso typed into the budget field
     *    and defeat the cache entirely.
     *  - The version segment makes invalidation possible. Bucketed keys cannot be
     *    enumerated cheaply, so bumping the counter retires every bucket at once
     *    instead of leaving stale catalogues behind after a re-seed.
     *
     * @param  int  $allowanceBucket Allowance rounded down to a PHP 50 step.
     */
    public static function getBudgetFriendlyPlaces(int $allowanceBucket, callable $fetcher): array
    {
        $cacheKey = sprintf('planora_places_v%d_%d', self::placesCacheVersion(), $allowanceBucket);

        return Cache::remember($cacheKey, self::TTL['places'], function () use ($fetcher) {
            Log::debug('[CacheService] Cache miss — reading the place catalogue from the DB');
            return $fetcher();
        });
    }

    /**
     * Retire every cached place catalogue. Call after the `locations` rows change.
     */
    public static function clearPlacesCache(): void
    {
        Cache::forever('planora_places_version', self::placesCacheVersion() + 1);
        Log::debug('[CacheService] Place catalogue cache retired');
    }

    private static function placesCacheVersion(): int
    {
        return (int) Cache::rememberForever('planora_places_version', fn () => 1);
    }

    /**
     * Fetch hotels with caching.
     */
    public static function getHotels(callable $fetcher): array
    {
        $cacheKey = 'planora_hotels_all';

        return Cache::remember($cacheKey, self::TTL['hotels'], function () use ($fetcher) {
            Log::debug('[CacheService] Cache miss — fetching hotels from DB');
            $hotels = $fetcher();
            return is_array($hotels) ? $hotels : $hotels->toArray();
        });
    }

    /**
     * Invalidate the hotels cache (call after create/update/delete).
     */
    public static function clearHotelsCache(): void
    {
        Cache::forget('planora_hotels_all');
        Log::debug('[CacheService] Hotels cache cleared');
    }

    /**
     * Fetch weather data with caching.
     */
    public static function getWeather(float $lat, float $lon, callable $fetcher): ?array
    {
        $cacheKey = sprintf('planora_weather_%.4f_%.4f', $lat, $lon);

        return Cache::remember($cacheKey, self::TTL['weather'], function () use ($fetcher) {
            Log::debug('[CacheService] Cache miss — fetching weather from OpenWeatherMap');
            return $fetcher();
        });
    }

    /**
     * Fetch nearby places (Overpass API) with caching.
     */
    public static function getNearbyPlaces(float $lat, float $lon, callable $fetcher): array
    {
        $cacheKey = sprintf('planora_nearby_%.4f_%.4f', $lat, $lon);

        return Cache::remember($cacheKey, self::TTL['nearby'], function () use ($fetcher) {
            Log::debug('[CacheService] Cache miss — fetching nearby places from Overpass');
            return $fetcher();
        });
    }

    /**
     * Invalidate location-based caches (useful if admin updates POI data).
     */
    public static function clearLocationCache(float $lat, float $lon): void
    {
        Cache::forget(sprintf('planora_weather_%.4f_%.4f', $lat, $lon));
        Cache::forget(sprintf('planora_nearby_%.4f_%.4f', $lat, $lon));
    }
}