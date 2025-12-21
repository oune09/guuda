<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceCors
{
    public function handle(Request $request, Closure $next)
    {
        // Headers CORS FORCÉS
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        // Si OPTIONS, réponse immédiate
        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200, $headers);
        }

        $response = $next($request);
        
        // Force les headers sur toutes les réponses
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}