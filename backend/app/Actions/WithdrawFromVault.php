<?php

namespace App\Actions;

use App\Enums\MovementType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

class WithdrawFromVault
{
    // CORREÇÃO 2: update atômico. Conferir e descontar viram um único comando SQL:
    //   UPDATE vaults SET balance_cents = balance_cents - ? WHERE id = ? AND balance_cents >= ?
    // O Postgres tranca a linha sozinho durante o UPDATE; quem chega depois espera e, quando
    // roda, reavalia o WHERE sobre o saldo já descontado. Se o WHERE não bate, zero linhas mudam.
    public function handle(Vault $vault, int $amountCents): Vault
    {
        // a transação continua: saldo e movimentação mudam juntos ou não mudam
        return DB::transaction(function () use ($vault, $amountCents) {
            $changed = Vault::whereKey($vault->getKey())
                ->where('balance_cents', '>=', $amountCents)   // a conferência vai dentro do SQL
                ->decrement('balance_cents', $amountCents);    // devolve quantas linhas mudaram

            if ($changed === 0) {
                throw new InsufficientBalanceException();      // nenhuma linha: faltou saldo
            }

            $vault->movements()->create(['type' => MovementType::Withdraw, 'amount_cents' => $amountCents]);

            return $vault->refresh();
        });
    }
}
