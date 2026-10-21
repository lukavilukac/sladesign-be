<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Closure;
use const http\Client\Curl\AUTH_ANY;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        //todo - implement logic for checking if user is admin
        if (Auth::user() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        return redirect('/login');
    }
}
