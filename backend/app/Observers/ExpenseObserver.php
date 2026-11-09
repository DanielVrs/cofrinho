<?php

namespace App\Observers;

use App\Actions\SummarizeMonth;
use App\Models\Expense;

// Sempre que um lançamento muda, o resumo guardado daquele mês ficou velho: apaga a chave.
// O Eloquent chama estes métodos sozinho (ver #[ObservedBy] no model).
class ExpenseObserver
{
    // saved cobre criar e editar
    public function saved(Expense $expense): void
    {
        SummarizeMonth::forget($expense->user_id, $expense->spent_on->format('Y-m'));

        // editou a data e trocou de mês: o resumo do mês antigo também mudou
        if ($expense->wasChanged('spent_on')) {
            $before = $expense->getOriginal('spent_on');

            if ($before) {
                SummarizeMonth::forget($expense->user_id, $before->format('Y-m'));
            }
        }
    }

    public function deleted(Expense $expense): void
    {
        SummarizeMonth::forget($expense->user_id, $expense->spent_on->format('Y-m'));
    }
}
