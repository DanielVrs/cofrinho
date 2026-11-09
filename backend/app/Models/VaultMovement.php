<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultMovement extends Model
{
    protected $fillable = ['vault_id', 'type', 'amount_cents'];

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'amount_cents' => 'integer',
        ];
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }
}
