<?php

namespace App\Http\Controllers;

use App\Actions\ExportExpensesCsv;
use App\Http\Requests\ExportExpensesRequest;

class ExportExpensesController extends Controller
{
    public function __invoke(ExportExpensesRequest $request, ExportExpensesCsv $exportExpensesCsv)
    {
        $month = $request->validated('month');

        return response($exportExpensesCsv->handle($request->user(), $month), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"lancamentos-{$month}.csv\"",
        ]);
    }
}
