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
        'geocode'     => 604800,// 1 week — coordinates don't change
    ];

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