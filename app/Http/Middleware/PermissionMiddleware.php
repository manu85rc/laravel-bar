<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        if ($user->isUser()) {
            return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        $home = $user->panelHomeRoute($permission);

        return ($home ? redirect()->route($home) : redirect('/'))
            ->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
