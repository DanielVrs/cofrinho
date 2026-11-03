<?php

namespace App\Actions;

use App\Models\Expense;

class SummarizeMonth
{
    public function handle(string $month): array
    {
        // SUM e GROUP BY rodam no banco; o PHP só recebe o resultado pronto
        $byCategory = Expense::query()
            ->forMonth($month)
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->selectRaw('expenses.category_id, categories.name, SUM(expenses.amount_cents) as total_cents')
            ->groupBy('expenses.category_id', 'categories.name')
            ->orderByDesc('total_cents')
            ->get();

        return [
            'month' => $month,
            'total_cents' => (int) Expense::query()->forMonth($month)->sum('amount_cents'),
            'by_category' => $byCategory->map(fn ($row) => [
                'category_id' => $row->category_id,
                'name' => $row->name,
                'total_cents' => (int) $row->total_cents,
            ])->all(),
        ];
    }
}
