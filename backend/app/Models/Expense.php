<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'amount_cents', 'spent_on', 'category_id', 'user_id'];

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

    // Expense::forMonth('2026-11') filtra do dia 1 ao último dia do mês
    #[Scope]
    protected function forMonth(Builder $query, string $month): void
    {
        $start = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();

        $query->whereBetween('spent_on', [$start->toDateString(), $start->endOfMonth()->toDateString()]);
    }

    #[Scope]
    protected function forCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
