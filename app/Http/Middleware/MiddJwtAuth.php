<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class MiddJwtAuth
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Tenta autenticar o usuário
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Credenciais inválidas'], 404);
            }
            
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Acesso expirado'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Usuário não autorizado'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
        // Adiciona o usuário ao request para ser acessado nas rotas
        $request->attributes->set('user', $user);
        
        return $next($request);
    }
}