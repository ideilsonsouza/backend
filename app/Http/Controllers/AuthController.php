<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Autentica o usuário e gera um token JWT.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * Requerimentos:
     * - Email: O email do usuário.
     * - Senha: A senha do usuário.
     *
     * Retornos:
     * - 200: Retorna o token JWT e as informações do usuário autenticado.
     * - 401: Credenciais inválidas ou usuário não autorizado.
     * - 422: Erro de validação dos dados.
     * - 500: Falha ao gerar o token.
     */
    public function login(Request $request): JsonResponse
    {
        // Validação dos dados de login
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Se a validação falhar, retorna erros
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        // Pega as credenciais (email e senha) e transforma o email em minúsculas
        $credentials = $request->only('email', 'password');
        $credentials['email'] = strtolower($credentials['email']);

        try {
            // Tenta autenticar e gerar o token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Credenciais inválidas'], 401);
            }


            $refresh_token = JWTAuth::claims(['type' => 'refresh'])->attempt($credentials);
            $expires_in = JWTAuth::factory()->getTTL() * 60;
            $token_type = 'bearer';

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Verifica se o usuário está habilitado
            if (!$user->enabled) {
                return response()->json(['message' => 'Usuário não autorizado'], 401);
            }
        } catch (JWTException $e) {
            // Se ocorrer um erro na geração do token, retorna erro 500
            return response()->json(['message' => 'Não foi possível criar o token'], 500);
        }
        // Retorna o usuário e o token gerado
        return response()->json(compact('user', 'token', 'expires_in', 'token_type', 'refresh_token'), 200);
    }

    /**
     * Registra um novo usuário no sistema e gera um token JWT.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * Requerimentos:
     * - Nome: O nome completo do usuário.
     * - Email: O email do usuário (deve ser único).
     * - Senha: A senha do usuário (deve ser confirmada).
     * - Confirmação de senha: Deve corresponder à senha.
     *
     * Retornos:
     * - 201: Usuário criado com sucesso e token JWT retornado.
     * - 422: Erro de validação dos dados.
     */
    public function register(Request $request): JsonResponse
    {
        // Validação dos dados de registro
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        // Se a validação falhar, retorna erros
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        // Cria o usuário no banco de dados
        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),  // Padroniza o email para minúsculas
            'password' => bcrypt($request->password),  // Criptografa a senha
            'definers' =>  $request->definers,
        ]);

        // Gera o token JWT para o novo usuário
        $token = JWTAuth::fromUser($user);

        // Retorna o usuário e o token gerado
        return response()->json(compact('user', 'token'), 201);
    }
    /**
     * Retorna os detalhes do usuário autenticado.
     *
     * @return JsonResponse
     *
     * Requisitos:
     * - Token JWT válido no header de autorização.
     *
     * Retornos:
     * - 200: Retorna as informações do usuário autenticado.
     * - 401: Caso o token JWT seja inválido ou o usuário não esteja autenticado.
     * - 500: Se houver falha ao obter o usuário.
     */
    public function me(): JsonResponse
    {
        try {
            // Tenta obter o usuário autenticado
            $user = Auth::user();

            // Verifica se o usuário foi encontrado
            if (!$user) {
                return response()->json(['message' => 'Usuário não autenticado'], 401);
            }

            // Retorna o usuário autenticado
            return response()->json(compact('user'), 200);
        } catch (\Exception $e) {
            // Tratamento de exceção para falhas inesperadas
            return response()->json([
                'message' => 'Erro ao obter o usuário autenticado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
