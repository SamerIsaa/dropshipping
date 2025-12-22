<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['en', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $sessionLocale = null;
        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
        }

        $locale = $sessionLocale
            ?? $request->cookie('locale')
            ?? $this->resolveFromHeader($request->header('Accept-Language', ''));

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function resolveFromHeader(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        $candidates = array_map(static function ($part) {
            $segment = trim($part);
            $segment = explode(';', $segment)[0] ?? '';
            return strtolower($segment);
        }, explode(',', $header));

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }
            $base = substr($candidate, 0, 2);
            if (in_array($base, self::SUPPORTED_LOCALES, true)) {
                return $base;
            }
        }

        return null;
    }
}
