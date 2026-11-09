<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SummarizeMonth
{
    // TTL: depois de 10 minutos a chave some sozinha, mesmo que ninguém a apague
    public const TTL_MINUTES = 10;

    public function handle(User $user, string $month): array
    {
        // primeira chamada roda a consulta e guarda; as seguintes devolvem o guardado
        return Cache::remember(
            self::cacheKey($user->id, $month),
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->summarize($user, $month),
        );
    }

    // o id do usuário na chave impede que uma conta receba o resumo da outra
    public static function cacheKey(int $userId, string $month): string
    {
        return "summary:{$userId}:{$month}";
    }

    // invalidação: chamado quando um lançamento daquele mês muda
    public static function forget(int $userId, string $month): void
    {
        Cache::forget(self::cacheKey($userId, $month));
    }

    private function summarize(User $user, string $month): array
    {
        // SUM e GROUP BY rodam no banco; o PHP só recebe o resultado pronto
        $byCategory = $user->expenses()
            ->forMonth($month)
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->selectRaw('expenses.category_id, categories.name, categories.budget_cents, SUM(expenses.amount_cents) as total_cents')
            ->groupBy('expenses.category_id', 'categories.name', 'categories.budget_cents')
            ->orderByDesc('total_cents')
            ->get();

        return [
            'month' => $month,
            'total_cents' => (int) $user->expenses()->forMonth($month)->sum('amount_cents'),
            'by_category' => $byCategory->map(fn ($row) => [
                'category_id' => $row->category_id,
                'name' => $row->name,
                'total_cents' => (int) $row->total_cents,
                'budget_cents' => $row->budget_cents === null ? null : (int) $row->budget_cents,
                // alerta: passou do limite da categoria
                'over_budget' => $row->budget_cents !== null && (int) $row->total_cents > (int) $row->budget_cents,
            ])->all(),
        ];
    }
}
