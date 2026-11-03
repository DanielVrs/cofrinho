<?php

namespace App\Http\Controllers;

use App\Actions\RegisterUser;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $registerUser)
    {
        // cria o usuário (senha vira hash pelo cast do model) e as categorias padrão
        $user = $registerUser->handle($request->validated());

        return $this->respondWithToken($user, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        // Hash::check compara a senha digitada com o hash guardado
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha inválidos.'],
            ]);
        }

        return $this->respondWithToken($user);
    }

    public function logout(Request $request)
    {
        // apaga só o token usado nesta chamada
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    private function respondWithToken(User $user, int $status = Response::HTTP_OK)
    {
        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('cofrinho')->plainTextToken,
        ], $status);
    }
}
