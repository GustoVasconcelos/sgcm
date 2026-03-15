<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // --- DEFINIÇÃO DOS APELIDOS DE SEGURANÇA (Spatie) ---
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        // ----------------------------------------------------
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // --- TRATAMENTO DE EXCEÇÕES ---
        // -- TOKEN MISMATCH ERROR 419 --
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->withErrors(['error' => 'Sua sessão expirou. Por favor, faça login novamente.']);
        });

        // -- FALLBACK: HTTP EXCEPTION 419 --
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->route('login')->withErrors(['error' => 'Sua sessão expirou. Por favor, faça login novamente.']);
            }
        });
        // -----------------------------------
    })->create();