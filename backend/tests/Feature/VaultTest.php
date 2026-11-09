<?php

use App\Models\User;
use App\Models\Vault;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function ownVault($test, int $balance = 0): Vault
{
    return Vault::factory()->for($test->user)->create(['balance_cents' => $balance]);
}

test('cria um cofre com saldo zero', function () {
    $this->postJson('/api/vaults', ['name' => 'Viagem'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Viagem')
        ->assertJsonPath('data.balance_cents', 0);
});

test('depósito aumenta o saldo e registra a movimentação', function () {
    $vault = ownVault($this);

    $this->postJson("/api/vaults/{$vault->id}/deposit", ['amount_cents' => 10000])
        ->assertOk()
        ->assertJsonPath('data.balance_cents', 10000);

    $this->assertDatabaseHas('vault_movements', ['vault_id' => $vault->id, 'type' => 'deposit', 'amount_cents' => 10000]);
});

test('saque diminui o saldo e registra a movimentação', function () {
    $vault = ownVault($this, 10000);

    $this->postJson("/api/vaults/{$vault->id}/withdraw", ['amount_cents' => 8000])
        ->assertOk()
        ->assertJsonPath('data.balance_cents', 2000);

    $this->assertDatabaseHas('vault_movements', ['vault_id' => $vault->id, 'type' => 'withdraw', 'amount_cents' => 8000]);
});

test('saque maior que o saldo responde 422 e não registra nada', function () {
    $vault = ownVault($this, 5000);

    $this->postJson("/api/vaults/{$vault->id}/withdraw", ['amount_cents' => 8000])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount_cents');

    expect($vault->fresh()->balance_cents)->toBe(5000);
    $this->assertDatabaseCount('vault_movements', 0);
});

test('o saldo bate com a soma das movimentações', function () {
    $vault = ownVault($this);

    $this->postJson("/api/vaults/{$vault->id}/deposit", ['amount_cents' => 10000]);
    $this->postJson("/api/vaults/{$vault->id}/withdraw", ['amount_cents' => 3000]);

    $this->getJson("/api/vaults/{$vault->id}")
        ->assertOk()
        ->assertJsonPath('data.balance_cents', 7000)
        ->assertJsonPath('data.movements_total_cents', 7000);
});

test('valor do depósito e do saque precisa ser inteiro positivo', function () {
    $vault = ownVault($this);

    $this->postJson("/api/vaults/{$vault->id}/deposit", ['amount_cents' => 0])->assertJsonValidationErrors('amount_cents');
    $this->postJson("/api/vaults/{$vault->id}/withdraw", ['amount_cents' => 'dez'])->assertJsonValidationErrors('amount_cents');
});

test('não mexe no cofre de outro usuário', function () {
    $vault = Vault::factory()->create(['balance_cents' => 10000]);

    $this->getJson("/api/vaults/{$vault->id}")->assertForbidden();
    $this->postJson("/api/vaults/{$vault->id}/deposit", ['amount_cents' => 100])->assertForbidden();
    $this->postJson("/api/vaults/{$vault->id}/withdraw", ['amount_cents' => 100])->assertForbidden();
    $this->getJson('/api/vaults')->assertOk()->assertJsonCount(0, 'data');
});
