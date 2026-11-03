<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    public function index(IndexExpenseRequest $request)
    {
        $month = $request->validated('month');
        $categoryId = $request->validated('category_id');

        // with('category'): carrega todas as categorias em uma consulta só (evita o N+1)
        // when(): só aplica o filtro se o parâmetro veio na query string
        $expenses = Expense::with('category')
            ->when($month, fn ($query) => $query->forMonth($month))
            ->when($categoryId, fn ($query) => $query->forCategory((int) $categoryId))
            ->orderByDesc('spent_on')
            ->orderByDesc('id')
            ->paginate(15);

        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated())->load('category');

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Expense $expense)
    {
        return new ExpenseResource($expense->load('category'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return new ExpenseResource($expense->load('category'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return response()->noContent();
    }
}
