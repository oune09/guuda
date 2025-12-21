<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        
    ];

    protected $middlewareGroups = [
        'web' => [
            // ...
        ],

        'api' => [
            // ⚠️ AJOUTEZ votre Cors ici aussi
            \Illuminate\Http\Middleware\HandleCors::class,


            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\VerifyRole::class,
    ];
}