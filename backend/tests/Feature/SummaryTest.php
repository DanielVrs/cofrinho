<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('resume o mês com total geral e por categoria', function () {
    $mercado = Category::factory()->for($this->user)->create(['name' => 'Mercado']);
    $lazer = Category::factory()->for($this->user)->create(['name' => 'Lazer']);

    Expense::factory()->create(['category_id' => $mercado->id, 'amount_cents' => 1000, 'spent_on' => '2026-11-01']);
    Expense::factory()->create(['category_id' => $mercado->id, 'amount_cents' => 2500, 'spent_on' => '2026-11-15']);
    Expense::factory()->create(['category_id' => $lazer->id, 'amount_cents' => 500, 'spent_on' => '2026-11-20']);
    // de outro mês: não pode entrar na conta
    Expense::factory()->create(['category_id' => $mercado->id, 'amount_cents' => 9999, 'spent_on' => '2026-10-20']);

    $this->getJson('/api/summary?month=2026-11')
        ->assertOk()
        ->assertJsonPath('data.month', '2026-11')
        ->assertJsonPath('data.total_cents', 4000)
        ->assertJsonPath('data.by_category.0.name', 'Mercado')
        ->assertJsonPath('data.by_category.0.total_cents', 3500)
        ->assertJsonPath('data.by_category.1.name', 'Lazer')
        ->assertJsonPath('data.by_category.1.total_cents', 500);
});

test('resumo exige o mês', function () {
    $this->getJson('/api/summary')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
});

test('o resumo avisa quando a categoria passa do limite mensal', function () {
    $mercado = Category::factory()->for($this->user)->create(['name' => 'Mercado', 'budget_cents' => 3000]);
    $lazer = Category::factory()->for($this->user)->create(['name' => 'Lazer', 'budget_cents' => 5000]);
    $semLimite = Category::factory()->for($this->user)->create(['name' => 'Outros']);

    Expense::factory()->create(['category_id' => $mercado->id, 'amount_cents' => 3500, 'spent_on' => '2026-11-10']);
    Expense::factory()->create(['category_id' => $lazer->id, 'amount_cents' => 1000, 'spent_on' => '2026-11-10']);
    Expense::factory()->create(['category_id' => $semLimite->id, 'amount_cents' => 100, 'spent_on' => '2026-11-10']);

    $this->getJson('/api/summary?month=2026-11')
        ->assertOk()
        ->assertJsonPath('data.by_category.0.name', 'Mercado')
        ->assertJsonPath('data.by_category.0.budget_cents', 3000)
        ->assertJsonPath('data.by_category.0.over_budget', true)
        ->assertJsonPath('data.by_category.1.over_budget', false)
        ->assertJsonPath('data.by_category.2.budget_cents', null)
        ->assertJsonPath('data.by_category.2.over_budget', false);
});
