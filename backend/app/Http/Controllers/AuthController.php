<?php

namespace App\Http\Controllers;

use App\Actions\RegisterUser;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $registerUser)
    {
        // cria o usuário (senha vira hash pelo cast do model) e as categorias padrão
        $user = $registerUser->handle($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        // Auth::attempt confere e-mail e senha (Hash::check por baixo) e abre a sessão
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha inválidos.'],
            ]);
        }

        // troca o id da sessão depois do login (evita session fixation)
        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // apaga a sessão e gera um token CSRF novo
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
