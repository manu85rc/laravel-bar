<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $roleList = explode(',', $roles);

        foreach ($roleList as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        if ($user->isUser()) {
            return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        $home = $user->panelHomeRoute();

        return ($home ? redirect()->route($home) : redirect('/'))
            ->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
