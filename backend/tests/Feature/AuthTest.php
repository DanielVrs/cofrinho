<?php

use App\Models\User;

test('registra um usuário e abre a sessão', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Ana',
        'email' => 'ana@example.com',
        'password' => 'senha-forte',
        'password_confirmation' => 'senha-forte',
    ]);

    $response->assertCreated()->assertJsonPath('data.email', 'ana@example.com');

    // a senha nunca aparece na resposta
    expect($response->json('data'))->not->toHaveKey('password');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
});

test('conta nova já nasce com as categorias padrão', function () {
    $this->postJson('/api/register', [
        'name' => 'Ana',
        'email' => 'ana@example.com',
        'password' => 'senha-forte',
        'password_confirmation' => 'senha-forte',
    ])->assertCreated();

    $this->getJson('/api/categories')->assertOk()->assertJsonCount(5, 'data');
});

test('não registra com e-mail repetido', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Ana',
        'email' => 'ana@example.com',
        'password' => 'senha-forte',
        'password_confirmation' => 'senha-forte',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('faz login com senha certa', function () {
    $user = User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'senha-forte'])
        ->assertOk()
        ->assertJsonPath('data.email', 'ana@example.com');

    $this->assertAuthenticatedAs($user);
});

test('não faz login com senha errada', function () {
    User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'outra'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

test('a sessão aberta no login dá acesso às rotas protegidas', function () {
    User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'senha-forte'])->assertOk();

    $this->getJson('/api/user')->assertOk()->assertJsonPath('data.email', 'ana@example.com');
});

test('responde 401 sem sessão', function () {
    $this->getJson('/api/categories')->assertUnauthorized();
    $this->getJson('/api/expenses')->assertUnauthorized();
    $this->getJson('/api/summary?month=2026-11')->assertUnauthorized();
    $this->getJson('/api/user')->assertUnauthorized();
});

test('faz logout e a sessão deixa de valer', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/logout')->assertNoContent();

    $this->assertGuest('web');

    // no teste os guards ficam em memória entre requisições; esquecê-los simula uma requisição nova
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/user')->assertUnauthorized();
});

test('a senha fica guardada como hash', function () {
    User::factory()->create(['password' => 'senha-forte']);

    $user = User::first();

    expect($user->password)->not->toBe('senha-forte');
    expect(Hash::check('senha-forte', $user->password))->toBeTrue();
});
