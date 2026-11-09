<?php

namespace App\Actions;

use App\Enums\MovementType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Vault;

class WithdrawFromVault
{
    // VERSÃO INGÊNUA, de propósito: lê, confere, calcula, salva.
    // Duas requisições ao mesmo tempo leem o mesmo saldo e as duas passam na conferência.
    public function handle(Vault $vault, int $amountCents): Vault
    {
        $balance = $vault->balance_cents;             // 1. lê

        if ($balance < $amountCents) {                // 2. confere
            throw new InsufficientBalanceException();
        }

        usleep(300000); // TEMPORÁRIO: 0,3 s pra alargar a janela entre conferir e salvar

        $vault->update(['balance_cents' => $balance - $amountCents]);   // 3. calcula e salva
        $vault->movements()->create(['type' => MovementType::Withdraw, 'amount_cents' => $amountCents]);

        return $vault->refresh();
    }
}
