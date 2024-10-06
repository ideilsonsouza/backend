<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthTeam extends JwtAuthUser
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
        parent::handle($request, $next);

        $user = $request->attributes->get('user');

        // Verifica se o campo 'team' do usuário está true
        if (!$user->team) {
            return response()->json(['message' => 'Usuário não autorizado'], 403);
        }

        return $next($request);
    }
}