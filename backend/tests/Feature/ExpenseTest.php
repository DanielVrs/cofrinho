<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

// atalho: um lançamento numa categoria do usuário autenticado
function ownExpense(array $attributes = []): Expense
{
    $category = Category::factory()->for(test()->user)->create();

    return Expense::factory()->create(['category_id' => $category->id, ...$attributes]);
}

test('cria um lançamento', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->postJson('/api/expenses', [
        'description' => 'Almoço',
        'amount_cents' => 3250,
        'spent_on' => '2026-11-03',
        'category_id' => $category->id,
    ])
        ->assertCreated()
        ->assertJsonPath('data.amount_cents', 3250)
        ->assertJsonPath('data.category.name', $category->name);
});

test('não cria lançamento com categoria inexistente', function () {
    $this->postJson('/api/expenses', [
        'description' => 'Almoço',
        'amount_cents' => 3250,
        'spent_on' => '2026-11-03',
        'category_id' => 999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('não cria lançamento com valor zero', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->postJson('/api/expenses', [
        'description' => 'Almoço',
        'amount_cents' => 0,
        'spent_on' => '2026-11-03',
        'category_id' => $category->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount_cents');
});

test('filtra lançamentos por mês', function () {
    ownExpense(['description' => 'De novembro', 'spent_on' => '2026-11-10']);
    ownExpense(['description' => 'De outubro', 'spent_on' => '2026-10-10']);

    $this->getJson('/api/expenses?month=2026-11')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.description', 'De novembro');
});

test('filtra lançamentos por categoria', function () {
    $mercado = Category::factory()->for($this->user)->create();
    $lazer = Category::factory()->for($this->user)->create();
    Expense::factory()->count(2)->create(['category_id' => $mercado->id]);
    Expense::factory()->create(['category_id' => $lazer->id]);

    $this->getJson("/api/expenses?category_id={$mercado->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('rejeita mês inválido', function () {
    $this->getJson('/api/expenses?month=novembro')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
});

test('pagina a listagem com 15 por página', function () {
    Expense::factory()->count(20)->create(['category_id' => Category::factory()->for($this->user)->create()->id]);

    $this->getJson('/api/expenses')
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.total', 20);
});

test('remove um lançamento', function () {
    $expense = ownExpense();

    $this->deleteJson("/api/expenses/{$expense->id}")->assertNoContent();

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});
