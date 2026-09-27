<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED_LOCALES = ['ar', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->preferredLocale($request));

        return $next($request);
    }

    private function preferredLocale(Request $request): string
    {
        foreach ($request->getLanguages() as $language) {
            $locale = strtolower(explode('_', $language, 2)[0]);

            if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
                return $locale;
            }
        }

        return (string) config('app.fallback_locale', 'en');
    }
}
