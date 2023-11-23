<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Obtener el idioma desde la sesión o establecer 'es' como predeterminado
        $locale = session('configuracion.idioma', 'es');

        // Establecer el idioma en la aplicación
        App::setLocale($locale);

        return $next($request);
    }
}
