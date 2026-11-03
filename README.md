# Cofrinho

App para registrar gastos pessoais: categorias, lançamentos, filtro por mês e resumo.
Backend em Laravel (API) e frontend em React. Projeto do teste técnico guiado.

## Estrutura

- `backend/`: API em Laravel 13 com banco SQLite.
- `frontend/`: interface em React 19 com Vite.
- `exercicios/`: exercícios em PHP puro.
- `APRENDIZADOS.md`: respostas e anotações de cada etapa.

## Requisitos

- PHP 8.3 ou superior, Composer e o instalador do Laravel. O jeito mais simples é o [php.new](https://php.new).
- Node.js 22 (LTS) e npm.
- Git.

## Rodando do zero

Backend, em um terminal:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

A API fica em `http://localhost:8000`. O seed cria uma conta de demonstração:
`demo@cofrinho.test` com senha `password`.

A autenticação é por cookie de sessão (modo SPA do Sanctum). O `.env` do backend
precisa saber o endereço do frontend em `FRONTEND_URL` e `SANCTUM_STATEFUL_DOMAINS`
(já vêm no `.env.example` apontando para `localhost:5173`). Abra o app sempre por
`http://localhost:5173`, não por `127.0.0.1`, senão o cookie não vale.

Frontend, em outro terminal:

```sh
cd frontend
npm install
npm run dev
```

A interface fica em `http://localhost:5173`.

## Testes

```sh
cd backend
php artisan test
```
