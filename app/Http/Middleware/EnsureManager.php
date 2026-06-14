<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManager
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isManager()) {
            abort(403, 'هذه الشاشة متاحة للمدير فقط.');
        }

        return $next($request);
    }
}
