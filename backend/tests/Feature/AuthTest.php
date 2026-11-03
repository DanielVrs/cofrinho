<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('registra um usuário e devolve o token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Ana',
        'email' => 'ana@example.com',
        'password' => 'senha-forte',
        'password_confirmation' => 'senha-forte',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'ana@example.com')
        ->assertJsonStructure(['token']);

    // a senha nunca aparece na resposta
    expect($response->json('user'))->not->toHaveKey('password');

    $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
});

test('conta nova já nasce com as categorias padrão', function () {
    $token = $this->postJson('/api/register', [
        'name' => 'Ana',
        'email' => 'ana@example.com',
        'password' => 'senha-forte',
        'password_confirmation' => 'senha-forte',
    ])->json('token');

    $this->withToken($token)->getJson('/api/categories')
        ->assertOk()
        ->assertJsonCount(5, 'data');
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
    User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'senha-forte'])
        ->assertOk()
        ->assertJsonPath('user.email', 'ana@example.com')
        ->assertJsonStructure(['token']);
});

test('não faz login com senha errada', function () {
    User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'outra'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('o token devolvido no login dá acesso às rotas protegidas', function () {
    User::factory()->create(['email' => 'ana@example.com', 'password' => 'senha-forte']);

    $token = $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'senha-forte'])
        ->json('token');

    $this->withToken($token)->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.email', 'ana@example.com');
});

test('responde 401 sem token', function () {
    $this->getJson('/api/categories')->assertUnauthorized();
    $this->getJson('/api/expenses')->assertUnauthorized();
    $this->getJson('/api/summary?month=2026-11')->assertUnauthorized();
    $this->getJson('/api/user')->assertUnauthorized();
});

test('faz logout e o token para de valer', function () {
    $user = User::factory()->create();
    $token = $user->createToken('teste')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('a senha fica guardada como hash', function () {
    Sanctum::actingAs(User::factory()->create(['password' => 'senha-forte']));

    $user = User::first();

    expect($user->password)->not->toBe('senha-forte');
    expect(Hash::check('senha-forte', $user->password))->toBeTrue();
});
