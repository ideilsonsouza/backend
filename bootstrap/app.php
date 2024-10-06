<?php

use App\Http\Middleware\JwtAuthSuper;
use App\Http\Middleware\JwtAuthTeam;
use App\Http\Middleware\JwtAuthUser;
use App\Http\Middleware\MiddJwtAuth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => MiddJwtAuth::class,
            'jwt.auth:user' => JwtAuthUser::class,
            'jwt.auth:team' => JwtAuthTeam::class,
            'jwt.auth:super' => JwtAuthSuper::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {

            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage(),], 401);
            }
        });
    })->create();
