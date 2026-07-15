<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * API rate-limiting middleware for Planora endpoints.
 * Protects external API integrations from abuse and excessive costs.
 */
class ThrottleApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 30, int $decayMinutes = 1): Response
    {
        $key = sprintf(
            'planora_api:%s:%s',
            $request->ip() ?? 'unknown',
            $request->path()
        );

        $executed = RateLimiter::attempt(
            $key,
            $maxAttempts,
            function () {},
            $decayMinutes
        );

        if (!$executed) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => 'Too many requests. Please wait before trying again.',
                'retry_after_seconds' => $seconds,
            ], 429);
        }

        return $next($request);
    }
}