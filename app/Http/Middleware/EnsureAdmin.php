<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Only application administrators may manage the movie catalog.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->usertype === 'admin', 403, 'Only administrators may manage movies.');

        return $next($request);
    }
}
