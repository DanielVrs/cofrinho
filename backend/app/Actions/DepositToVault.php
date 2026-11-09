<?php

namespace App\Actions;

use App\Enums\MovementType;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

class DepositToVault
{
    public function handle(Vault $vault, int $amountCents): Vault
    {
        // transação: ou o saldo e a movimentação mudam juntos, ou nada muda
        return DB::transaction(function () use ($vault, $amountCents) {
            $vault->increment('balance_cents', $amountCents);
            $vault->movements()->create(['type' => MovementType::Deposit, 'amount_cents' => $amountCents]);

            return $vault->refresh();
        });
    }
}
