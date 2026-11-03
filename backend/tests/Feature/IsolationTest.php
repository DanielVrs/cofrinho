<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

// usuário A autenticado; usuário B com dados próprios
beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();

    $this->categoryB = Category::factory()->for($this->userB)->create();
    $this->expenseB = Expense::factory()->create(['category_id' => $this->categoryB->id]);

    $this->actingAs($this->userA);
});

test('a listagem só mostra as categorias do próprio usuário', function () {
    Category::factory()->for($this->userA)->count(2)->create();

    $this->getJson('/api/categories')->assertOk()->assertJsonCount(2, 'data');
});

test('não vê, edita nem remove categoria de outro usuário', function () {
    $this->getJson("/api/categories/{$this->categoryB->id}")->assertForbidden();

    $this->putJson("/api/categories/{$this->categoryB->id}", ['name' => 'Invadida', 'color' => '#000000'])
        ->assertForbidden();

    $this->deleteJson("/api/categories/{$this->categoryB->id}")->assertForbidden();

    $this->assertDatabaseHas('categories', ['id' => $this->categoryB->id, 'name' => $this->categoryB->name]);
});

test('a listagem só mostra os lançamentos do próprio usuário', function () {
    $categoryA = Category::factory()->for($this->userA)->create();
    Expense::factory()->count(3)->create(['category_id' => $categoryA->id]);

    $this->getJson('/api/expenses')->assertOk()->assertJsonCount(3, 'data');
});

test('não vê, edita nem remove lançamento de outro usuário', function () {
    $this->getJson("/api/expenses/{$this->expenseB->id}")->assertForbidden();

    $this->putJson("/api/expenses/{$this->expenseB->id}", [
        'description' => 'Invadido',
        'amount_cents' => 1,
        'spent_on' => '2026-11-03',
        'category_id' => $this->categoryB->id,
    ])->assertForbidden();

    $this->deleteJson("/api/expenses/{$this->expenseB->id}")->assertForbidden();

    $this->assertDatabaseHas('expenses', ['id' => $this->expenseB->id]);
});

test('não cria lançamento em categoria de outro usuário', function () {
    $this->postJson('/api/expenses', [
        'description' => 'Almoço',
        'amount_cents' => 3250,
        'spent_on' => '2026-11-03',
        'category_id' => $this->categoryB->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('o resumo só soma os lançamentos do próprio usuário', function () {
    $categoryA = Category::factory()->for($this->userA)->create();
    Expense::factory()->create(['category_id' => $categoryA->id, 'amount_cents' => 1000, 'spent_on' => '2026-11-10']);
    Expense::factory()->create(['category_id' => $this->categoryB->id, 'amount_cents' => 9999, 'spent_on' => '2026-11-10']);

    $this->getJson('/api/summary?month=2026-11')
        ->assertOk()
        ->assertJsonPath('data.total_cents', 1000)
        ->assertJsonCount(1, 'data.by_category');
});

test('dois usuários podem ter categoria com o mesmo nome', function () {
    Category::factory()->for($this->userB)->create(['name' => 'Mercado']);

    $this->postJson('/api/categories', ['name' => 'Mercado', 'color' => '#22AA88'])->assertCreated();
});
