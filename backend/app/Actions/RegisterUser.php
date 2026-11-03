<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUser
{
    public function __construct(private CreateDefaultCategories $createDefaultCategories)
    {
    }

    public function handle(array $data): User
    {
        // transação: ou cria usuário e categorias, ou não cria nada
        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            $this->createDefaultCategories->handle($user);

            return $user;
        });
    }
}
