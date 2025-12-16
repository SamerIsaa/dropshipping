<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'POST') {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        $cacheKey = 'idempotency:' . sha1($request->path() . '|' . $key);

        if (Cache::has($cacheKey)) {
            return response()->json(['error' => 'Duplicate request'], Response::HTTP_CONFLICT);
        }

        $response = $next($request);

        Cache::put($cacheKey, true, now()->addMinutes(10));

        return $response;
    }
}
