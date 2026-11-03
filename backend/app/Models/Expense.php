<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['description', 'amount_cents', 'spent_on', 'category_id'];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'spent_on' => 'date',
        ];
    }

    // lado "N" da relação: cada lançamento pertence a uma categoria
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
