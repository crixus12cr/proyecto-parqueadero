<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verificar si el usuario está autenticado usando el Facade
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Obtener el usuario autenticado y verificar si tiene alguno de los roles
        if (!Auth::user()->tieneAlgunRol($roles)) {
            abort(403, 'No tienes autorización para acceder a esta página.');
        }

        return $next($request);
    }
}