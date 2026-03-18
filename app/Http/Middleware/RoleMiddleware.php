<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verifica si el usuario está autenticado
        if (!auth()->check()) {
            abort(403, 'No Autorizado');
        }

        // 2. Obtiene el usuario autenticado
        $user = auth()->user();

        // 3. Verifica si el usuario tiene uno de los roles permitidos
        if (!in_array($user->role, $roles)) {
            abort(403, 'Acceso Denegado');
        }

        // 4. Permite continuar la request
        return $next($request);
    }
}