# Cofrinho

App para registrar gastos pessoais: categorias, lançamentos, filtro por mês e resumo.
Backend em Laravel (API) e frontend em React. Projeto do teste técnico guiado.

## Estrutura

- `backend/`: API em Laravel 13. Banco Postgres no Docker (ou SQLite, sem Docker).
- `frontend/`: interface em React 19 com Vite.
- `exercicios/`: exercícios em PHP puro.
- `compose.yaml`: sobe a API, o Postgres e o pgAdmin com um comando.
- `APRENDIZADOS.md`: respostas e anotações de cada etapa.

## Requisitos

- Docker (Docker Desktop no Windows ou macOS, Docker Engine no Linux).
- Node.js 22 (LTS) e npm, para o frontend.
- Git.

Sem Docker, o backend também roda direto na máquina. Veja "Rodando sem Docker".

## Rodando do zero

Backend, na raiz do repositório:

```sh
cp backend/.env.example backend/.env
docker compose up --build -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

O `composer install` é preciso porque a pasta `backend/` do seu computador é
montada dentro do container e esconde o `vendor/` que veio na imagem.

Serviços:

- API em `http://localhost:8000`.
- pgAdmin em `http://localhost:5050` (login `admin@cofrinho.com`, senha `segredo`).
  Ao cadastrar o servidor, use host `db`, porta `5432`, banco, usuário e senha `cofrinho` / `cofrinho` / `segredo`.
- Postgres só na rede interna do Compose, no nome `db`.

O seed cria uma conta de demonstração: `demo@cofrinho.test` com senha `password`.

`docker compose down` para tudo e mantém os dados. `docker compose down -v` apaga
também os volumes, ou seja, o banco.

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

## Rodando sem Docker

Requer PHP 8.3 ou superior, Composer e o instalador do Laravel. O jeito mais
simples é o [php.new](https://php.new).

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

No `.env`, troque `DB_CONNECTION=pgsql` por `DB_CONNECTION=sqlite` e apague as
linhas `DB_HOST` a `DB_PASSWORD`.

## Testes

Os testes usam SQLite em memória, por isso rodam igual dentro ou fora do container.

```sh
docker compose exec app php artisan test
# ou, sem Docker:
cd backend && php artisan test
```
