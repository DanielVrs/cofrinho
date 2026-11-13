# Cofrinho

App para registrar gastos pessoais: categorias, lançamentos, filtro por mês e resumo.
Backend em Laravel (API) e frontend em React. Projeto do teste técnico guiado.

## Estrutura

- `backend/`: API em Laravel 13. Banco Postgres no Docker (ou SQLite, sem Docker).
- `frontend/`: interface em React 19 com Vite e TypeScript.
- `exercicios/`: exercícios em PHP puro.
- `scripts/`: experimentos de concorrência (race condition e clique duplo).
- `compose.yaml`: sobe a API, o worker da fila, o Postgres, o Redis e o pgAdmin com um comando.
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

- `app`: API em `http://localhost:8000`.
- `worker`: processa a fila (exportação de CSV). Mesma imagem do `app`, comando `php artisan queue:work`.
- `db`: Postgres 18, só na rede interna do Compose.
- `redis`: cache do resumo, fila e locks, só na rede interna.
- `pgadmin`: `http://localhost:5050` (login `admin@cofrinho.com`, senha `segredo`).
  Ao cadastrar o servidor, use host `db`, porta `5432`, banco, usuário e senha `cofrinho` / `cofrinho` / `segredo`.

O seed cria uma conta de demonstração: `demo@cofrinho.test` com senha `password`.

Para subir dois workers da fila ao mesmo tempo:

```sh
docker compose up -d --scale worker=2
```

O `POST /api/expenses` aceita o cabeçalho `Idempotency-Key` (o frontend manda um
UUID por envio). Repetir a requisição com a mesma chave devolve o lançamento já
criado, com o cabeçalho `Idempotent-Replayed: true`, em vez de criar outro.

Scripts de experimento, na raiz: `scripts/race-withdraw.sh` (saques simultâneos
num cofre) e `scripts/double-click.sh [--key]` (o mesmo lançamento duas vezes).

`docker compose down` para tudo e mantém os dados. `docker compose down -v` apaga
também os volumes, ou seja, o banco.

A autenticação é por cookie de sessão (modo SPA do Sanctum). O `.env` do backend
precisa saber o endereço do frontend em `FRONTEND_URL` e `SANCTUM_STATEFUL_DOMAINS`
(já vêm no `.env.example` apontando para `localhost:5173`). Abra o app sempre por
`http://localhost:5173`, não por `127.0.0.1`, senão o cookie não vale.

Frontend, em outro terminal:

```sh
cd frontend
cp .env.example .env
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

Sem Docker não há Postgres nem Redis, então ajuste o `.env` antes do `migrate`:

- `DB_CONNECTION=sqlite` e apague as linhas `DB_HOST` a `DB_PASSWORD`;
- `CACHE_STORE=database` (o cache do resumo e os locks vão para o banco);
- `QUEUE_CONNECTION=sync` (a exportação roda na própria requisição, sem worker).
  Para ver a fila de verdade, use `QUEUE_CONNECTION=database` e rode
  `php artisan queue:work` em outro terminal.

## API

Todas as rotas ficam sob `/api`. Fora `register`, `login` e `ping`, todas exigem sessão.

| Método e rota | O que faz |
|---|---|
| `POST /register`, `POST /login`, `POST /logout`, `GET /user` | conta e sessão (cookie do Sanctum) |
| `GET, POST /categories`, `GET, PUT, DELETE /categories/{id}` | categorias; remover categoria com lançamentos responde 409 |
| `GET, POST /expenses`, `GET, PUT, DELETE /expenses/{id}` | lançamentos; `GET` aceita `?month=2026-11&category_id=1&page=1` |
| `GET /summary?month=2026-11` | total do mês por categoria, com cache de 10 minutos no Redis |
| `POST /exports`, `GET /exports/{id}`, `GET /exports/{id}/download` | CSV do mês gerado na fila: responde 202 e o cliente consulta o status |
| `GET, POST /vaults`, `GET /vaults/{id}`, `POST /vaults/{id}/deposit`, `POST /vaults/{id}/withdraw` | cofres; saque acima do saldo responde 422 |

Valores em centavos (`amount_cents`). Erros de validação respondem 422 com `errors` por campo.

## Testes

Os testes usam SQLite em memória, por isso rodam igual dentro ou fora do container.

```sh
docker compose exec app php artisan test
# ou, sem Docker:
cd backend && php artisan test
```
