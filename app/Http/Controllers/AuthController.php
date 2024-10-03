<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $credenciais = $request->all(['email', 'password']);

        // Autenticação (email e senha);
        $token = auth('api')->attempt($credenciais);

        if($token) { // Usuário autenticado com sucesso;
            return response()->json(['msg' => 'Login autenticado com sucesso', 'token' => $token], 200);
        }
        else { // Erro de usuário ou senha;
            return response()->json(['erro' => 'Usuário ou Senha inválido'], 403);
        }
        
        // Retornar um Json Web Token (JWT);
    }

    public function logout() {
        Auth::logout();

        auth()->invalidate();

        return response()->json(['msg' => 'Logout foi realizado com sucesso!'], 200);
    }

    public function refresh() {
        $token = auth('api')->refresh(); // O cliente deve encaminhar um JWT válido;
        return response()->json(['token atualizado:' => $token]);
    }

    public function me() {
        return response()->json((auth()->user()));
    }
}
