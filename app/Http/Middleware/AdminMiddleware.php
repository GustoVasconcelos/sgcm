<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se está logado e se o perfil é admin
        if (Auth::check() && Auth::user()->profile === 'admin') {
            return $next($request);
        }

        // Se não for admin, redireciona para o dashboard comum com erro
        return redirect()->route('dashboard')->with('error', 'Acesso restrito a administradores.');
    }
}