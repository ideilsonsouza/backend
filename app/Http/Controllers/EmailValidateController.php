<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\EmailValidate;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;

class EmailValidateController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Tenta obter o usuário autenticado
        $user = JWTAuth::parseToken()->authenticate();

        $code = EmailValidate::generateCode();

        $emailValidate = EmailValidate::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'created_at' => Carbon::now()->addMinutes(30)
            ]
        );

        Mail::raw("Seu código de verificação é: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Código de Validação de E-mail');
        });

        return response()->json([
            'message' => 'O código de validação foi enviado para o e-mail.',
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailValidate $emailValidate)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        $emailValidate = EmailValidate::where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        // Verifica se o código existe e se não expirou
        if (!$emailValidate || $emailValidate->isExpired()) {
            return response()->json([
                'message' => 'Código inválido ou expirado.',
            ], 400);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $emailValidate->delete();

        return response()->json([
            'message' => 'O e-mail foi validado com sucesso.',
        ]);
    }
}
