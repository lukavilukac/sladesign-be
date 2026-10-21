<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $firstSegment = $request->segment(1);

        if ($firstSegment === 'home' || $request->header('Locale') === 'en') {
            App::setLocale('en');
        } else {
            App::setLocale('sr');
        }

        return $next($request);
    }
}
