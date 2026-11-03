<?php

namespace App\Actions;

use App\Models\User;

class SummarizeMonth
{
    public function handle(User $user, string $month): array
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
