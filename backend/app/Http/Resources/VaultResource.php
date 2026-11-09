<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'balance_cents' => $this->balance_cents,
            // soma das movimentações (depósitos menos saques): tem que bater com o saldo
            'movements_total_cents' => $this->when(
                isset($this->deposits_cents),
                fn () => (int) $this->deposits_cents - (int) $this->withdrawals_cents,
            ),
        ];
    }
}
