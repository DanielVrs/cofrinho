<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->category = Category::factory()->for($this->user)->create();
    $this->payload = [
        'description' => 'Almoço',
        'amount_cents' => 3250,
        'spent_on' => '2026-11-10',
        'category_id' => $this->category->id,
    ];
});

// POST /api/expenses com o cabeçalho Idempotency-Key
function postWithKey(array $payload, string $key)
{
    return test()->postJson('/api/expenses', $payload, ['Idempotency-Key' => $key]);
}

test('sem a chave, cada requisição cria um lançamento', function () {
    $this->postJson('/api/expenses', $this->payload)->assertCreated();
    $this->postJson('/api/expenses', $this->payload)->assertCreated();

    expect(Expense::count())->toBe(2);
});

test('duas requisições com a mesma chave criam um lançamento só', function () {
    $first = postWithKey($this->payload, 'chave-1')->assertCreated()->assertHeaderMissing('Idempotent-Replayed');
    $second = postWithKey($this->payload, 'chave-1')->assertCreated()->assertHeader('Idempotent-Replayed', 'true');

    expect(Expense::count())->toBe(1)
        ->and($second->json('data.id'))->toBe($first->json('data.id'));
});

test('a repetição devolve o lançamento original, mesmo com dados diferentes', function () {
    $first = postWithKey($this->payload, 'chave-1');
    $second = postWithKey([...$this->payload, 'amount_cents' => 9999], 'chave-1');

    expect(Expense::count())->toBe(1)
        ->and($second->json('data.id'))->toBe($first->json('data.id'))
        ->and($second->json('data.amount_cents'))->toBe(3250);
});

test('chaves diferentes criam lançamentos diferentes', function () {
    postWithKey($this->payload, 'chave-1')->assertCreated();
    postWithKey($this->payload, 'chave-2')->assertCreated();

    expect(Expense::count())->toBe(2);
});

test('a chave vale por usuário: outro usuário pode usar a mesma', function () {
    postWithKey($this->payload, 'chave-1')->assertCreated();

    $other = User::factory()->create();
    $otherCategory = Category::factory()->for($other)->create();
    $this->actingAs($other);
    postWithKey([...$this->payload, 'category_id' => $otherCategory->id], 'chave-1')
        ->assertCreated()
        ->assertHeaderMissing('Idempotent-Replayed');

    expect(Expense::count())->toBe(2);
});

test('chave com mais de 64 caracteres responde 422', function () {
    postWithKey($this->payload, str_repeat('a', 65))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('idempotency_key');
});

test('a chave não pode vir no corpo da requisição', function () {
    // sem cabeçalho, o idempotency_key do corpo é ignorado
    $this->postJson('/api/expenses', [...$this->payload, 'idempotency_key' => 'do-corpo'])->assertCreated();
    $this->postJson('/api/expenses', [...$this->payload, 'idempotency_key' => 'do-corpo'])->assertCreated();

    expect(Expense::count())->toBe(2);
});
