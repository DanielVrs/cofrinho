<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// todos os testes deste arquivo rodam com um usuário autenticado
beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('lista as categorias', function () {
    Category::factory()->for($this->user)->count(3)->create();

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('cria uma categoria com dados válidos', function () {
    $this->postJson('/api/categories', ['name' => 'Mercado', 'color' => '#22AA88'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Mercado');

    $this->assertDatabaseHas('categories', ['name' => 'Mercado']);
});

test('não cria categoria com nome repetido', function () {
    Category::factory()->for($this->user)->create(['name' => 'Mercado']);

    $this->postJson('/api/categories', ['name' => 'Mercado', 'color' => '#22AA88'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('não cria categoria com cor inválida', function () {
    $this->postJson('/api/categories', ['name' => 'Mercado', 'color' => 'verde'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('color');
});

test('responde 404 para categoria inexistente', function () {
    $this->getJson('/api/categories/999')->assertNotFound();
});

test('atualiza uma categoria', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->putJson("/api/categories/{$category->id}", ['name' => 'Novo nome', 'color' => '#000000'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Novo nome');
});

test('remove uma categoria sem lançamentos', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->deleteJson("/api/categories/{$category->id}")->assertNoContent();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('não remove categoria em uso', function () {
    $category = Category::factory()->for($this->user)->create();
    Expense::factory()->create(['category_id' => $category->id]);

    $this->deleteJson("/api/categories/{$category->id}")
        ->assertConflict()
        ->assertJsonPath('message', 'A categoria possui lançamentos e não pode ser removida.');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
