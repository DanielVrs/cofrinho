<?php

namespace App\Actions;

use App\Enums\MovementType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

class WithdrawFromVault
{
    // CORREÇÃO 1: transação + lockForUpdate.
    // O SELECT ... FOR UPDATE tranca a linha do cofre até o COMMIT. A segunda requisição
    // fica parada no SELECT e, quando consegue ler, já vê o saldo descontado pela primeira.
    public function handle(Vault $vault, int $amountCents): Vault
    {
        return DB::transaction(function () use ($vault, $amountCents) {
            $locked = Vault::whereKey($vault->getKey())->lockForUpdate()->firstOrFail(); // 1. lê trancando a linha

            if ($locked->balance_cents < $amountCents) {                                  // 2. confere
                throw new InsufficientBalanceException();                                 // rollback: nada foi escrito
            }

            usleep(300000); // TEMPORÁRIO: a janela continua larga, mas agora ninguém mais entra nela

            $locked->update(['balance_cents' => $locked->balance_cents - $amountCents]);  // 3. calcula e salva
            $locked->movements()->create(['type' => MovementType::Withdraw, 'amount_cents' => $amountCents]);

            return $locked;                                                               // COMMIT solta o lock
        });
    }
}
