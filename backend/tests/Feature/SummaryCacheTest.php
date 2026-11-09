<?php

use App\Actions\SummarizeMonth;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

function cachedSummaryTotal($test, string $month = '2026-11'): int
{
    return $test->getJson("/api/summary?month={$month}")->assertOk()->json('data.total_cents');
}

function expenseFor($test, int $cents, string $date = '2026-11-10'): Expense
{
    return Expense::factory()->for($test->user)->for($test->category)->create([
        'amount_cents' => $cents,
        'spent_on' => $date,
    ]);
}

test('guarda o resumo em cache e não volta ao banco na segunda chamada', function () {
    expenseFor($this, 1000);

    expect(cachedSummaryTotal($this))->toBe(1000)
        ->and(Cache::has(SummarizeMonth::cacheKey($this->user->id, '2026-11')))->toBeTrue();

    // insere direto no banco, sem disparar o observer: o cache não sabe desse lançamento
    Expense::withoutEvents(fn () => expenseFor($this, 2000));

    // resposta veio do cache, por isso ainda mostra 1000
    expect(cachedSummaryTotal($this))->toBe(1000);
});

test('criar um lançamento pela API atualiza o resumo na hora', function () {
    expect(cachedSummaryTotal($this))->toBe(0);

    $this->postJson('/api/expenses', [
        'description' => 'Mercado',
        'amount_cents' => 1500,
        'spent_on' => '2026-11-05',
        'category_id' => $this->category->id,
    ])->assertCreated();

    expect(cachedSummaryTotal($this))->toBe(1500);
});

test('editar a data de um lançamento atualiza o resumo dos dois meses', function () {
    $expense = expenseFor($this, 1000, '2026-11-10');

    expect(cachedSummaryTotal($this, '2026-11'))->toBe(1000)
        ->and(cachedSummaryTotal($this, '2026-12'))->toBe(0);

    $this->putJson("/api/expenses/{$expense->id}", [
        'description' => $expense->description,
        'amount_cents' => 1000,
        'spent_on' => '2026-12-01',
        'category_id' => $this->category->id,
    ])->assertOk();

    expect(cachedSummaryTotal($this, '2026-11'))->toBe(0)
        ->and(cachedSummaryTotal($this, '2026-12'))->toBe(1000);
});

test('remover um lançamento atualiza o resumo', function () {
    $expense = expenseFor($this, 1000);
    expect(cachedSummaryTotal($this))->toBe(1000);

    $this->deleteJson("/api/expenses/{$expense->id}")->assertNoContent();

    expect(cachedSummaryTotal($this))->toBe(0);
});

test('duas contas nunca recebem o resumo uma da outra', function () {
    expenseFor($this, 1000);
    expect(cachedSummaryTotal($this))->toBe(1000);

    $other = User::factory()->create();
    $this->actingAs($other);

    expect(cachedSummaryTotal($this))->toBe(0);
});
