<?php

namespace App\Actions;

use App\Models\User;

class ExportExpensesCsv
{
    // gera o CSV do mês no formato que o Excel em português abre direto:
    // separador ";", decimal com vírgula e BOM para os acentos
    public function handle(User $user, string $month): string
    {
        $expenses = $user->expenses()
            ->with('category')
            ->forMonth($month)
            ->orderBy('spent_on')
            ->orderBy('id')
            ->get();

        $buffer = fopen('php://temp', 'r+');

        fwrite($buffer, "\xEF\xBB\xBF"); // BOM UTF-8
        fputcsv($buffer, ['Data', 'Descrição', 'Categoria', 'Valor (R$)'], ';');

        foreach ($expenses as $expense) {
            fputcsv($buffer, [
                $expense->spent_on->format('d/m/Y'),
                $expense->description,
                $expense->category->name,
                number_format($expense->amount_cents / 100, 2, ',', ''),
            ], ';');
        }

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $csv;
    }
}
