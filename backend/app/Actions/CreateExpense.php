<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class CreateExpense
{
    // $data já vem validado pelo StoreExpenseRequest e pode trazer idempotency_key (do cabeçalho)
    public function handle(User $user, array $data): Expense
    {
        $key = $data['idempotency_key'] ?? null;

        try {
            // tenta inserir direto. Não é "confere se existe e depois insere": isso seriam dois
            // comandos, e duas requisições passariam na conferência ao mesmo tempo (Etapa 18).
            return $user->expenses()->create($data);
        } catch (UniqueConstraintViolationException $e) {
            if ($key === null) {
                throw $e;
            }

            // o índice único recusou o segundo insert: a chave já foi usada.
            // Devolve o lançamento que a primeira requisição criou (wasRecentlyCreated fica false).
            return $user->expenses()->where('idempotency_key', $key)->firstOrFail();
        }
    }
}
