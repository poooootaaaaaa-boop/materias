<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->user()?->role, ['teacher', 'admin'], true) || $request->user()?->is_admin, 403, 'Se requiere una cuenta de maestro.');
        return $next($request);
    }
}