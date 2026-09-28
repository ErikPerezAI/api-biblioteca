<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateJsonSyntax
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson() && !empty($request->getContent())) {
            json_decode($request->getContent());
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'mensaje' => 'Error de formato: JSON mal formado',
                    'error' => json_last_error_msg()
                ], 400);
            }
        }

        return $next($request);
    }
}
