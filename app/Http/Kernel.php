<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Spatie\Permission\Middleware\PermissionMiddleware as MiddlewarePermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware as MiddlewareRoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware as MiddlewareRoleOrPermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

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
        'role' => MiddlewareRoleMiddleware::class,
        'permission' => MiddlewarePermissionMiddleware::class,
        'role_or_permission' => MiddlewareRoleOrPermissionMiddleware::class,0000
    ];
}