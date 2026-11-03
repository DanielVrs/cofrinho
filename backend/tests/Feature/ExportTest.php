<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('exporta os lançamentos do mês em CSV', function () {
    $category = Category::factory()->for($this->user)->create(['name' => 'Mercado']);
    Expense::factory()->create(['category_id' => $category->id, 'description' => 'Feira', 'amount_cents' => 1250, 'spent_on' => '2026-11-05']);
    Expense::factory()->create(['category_id' => $category->id, 'description' => 'De outubro', 'spent_on' => '2026-10-05']);
    // de outro usuário: não pode aparecer
    Expense::factory()->create(['description' => 'Alheio', 'spent_on' => '2026-11-05']);

    $response = $this->get('/api/expenses/export?month=2026-11');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('lancamentos-2026-11.csv');

    $csv = $response->getContent();

    expect($csv)->toContain('Data;Descrição;Categoria;"Valor (R$)"')
        ->toContain('05/11/2026;Feira;Mercado;12,50')
        ->not->toContain('De outubro')
        ->not->toContain('Alheio');
});

test('exportação exige o mês', function () {
    $this->getJson('/api/expenses/export')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
});
