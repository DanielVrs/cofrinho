<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'budget_cents', 'user_id'];

    protected function casts(): array
    {
        return ['budget_cents' => 'integer'];
    }

    // lado "1" da relação: uma categoria tem vários lançamentos
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
