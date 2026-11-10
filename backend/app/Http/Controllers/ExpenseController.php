<?php

namespace App\Http\Controllers;

use App\Actions\CreateExpense;
use App\Http\Requests\IndexExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function index(IndexExpenseRequest $request)
    {
        $month = $request->validated('month');
        $categoryId = $request->validated('category_id');

        // parte da relação do usuário: só os lançamentos dele
        // with('category'): carrega todas as categorias em uma consulta só (evita o N+1)
        // when(): só aplica o filtro se o parâmetro veio na query string
        $expenses = $request->user()->expenses()
            ->with('category')
            ->when($month, fn ($query) => $query->forMonth($month))
            ->when($categoryId, fn ($query) => $query->forCategory((int) $categoryId))
            ->orderByDesc('spent_on')
            ->orderByDesc('id')
            ->paginate(15);

        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request, CreateExpense $createExpense)
    {
        $expense = $createExpense->handle($request->user(), $request->validated())->load('category');

        $response = (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        // repetição com a mesma Idempotency-Key: mesma resposta de antes, mais um cabeçalho avisando
        if (! $expense->wasRecentlyCreated) {
            $response->header('Idempotent-Replayed', 'true');
        }

        return $response;
    }

    public function show(Expense $expense)
    {
        Gate::authorize('view', $expense);

        return new ExpenseResource($expense->load('category'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        // a autorização de update está no UpdateExpenseRequest::authorize()
        $expense->update($request->validated());

        return new ExpenseResource($expense->load('category'));
    }

    public function destroy(Expense $expense)
    {
        Gate::authorize('delete', $expense);

        $expense->delete();

        return response()->noContent();
    }
}
