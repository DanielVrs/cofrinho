# Cofrinho

App de gastos pessoais. Backend em Laravel (API) na pasta `backend/`, frontend
em React com Vite na pasta `frontend/`, exercícios em PHP puro em `exercicios/`.
Projeto de um teste técnico guiado em 13 etapas; as respostas de cada etapa
ficam em `APRENDIZADOS.md`.

## Modo de trabalho

- O usuário não escreve código. Claude escreve tudo e, a cada etapa ou quando
  for necessário, explica o que foi feito de forma curta, objetiva e simples.
- Um commit por etapa, no mínimo. Antes de cada commit, atualizar
  `APRENDIZADOS.md` com a resposta da etapa e pedir permissão.
- Conferir a documentação oficial do Laravel 13 e do React 19 quando houver
  dúvida de versão.

## Arquitetura: Clean Architecture pragmática, no padrão da comunidade Laravel

Regra de negócio isolada do HTTP e do framework, sem brigar com o Eloquent.

### Backend (`backend/app/`)

```
Actions/        casos de uso, uma classe por operação (CreateExpense, DeleteCategory)
Exceptions/     erros de domínio (CategoryInUseException -> 409)
Http/
  Controllers/  finos: recebem o request, chamam uma Action, devolvem um Resource
  Requests/     validação de entrada (Form Requests), nunca no controller
  Resources/    formato de saída (API Resources), sempre
Models/         Eloquent: entidades, relacionamentos, casts, scopes de consulta
Policies/       autorização por usuário
```

- Controller não tem regra de negócio; só orquestra.
- Regra de negócio fica em uma Action. Exemplo: "categoria com lançamentos
  não pode ser removida" vive em `DeleteCategory`, que lança
  `CategoryInUseException`, convertida em 409.
- Consultas ficam no model como scopes (`forMonth()`, `forCategory()`).
  Agregações (resumo) são feitas no banco, nunca em loop no PHP.
- Listagens com relacionamento usam eager loading (`with(...)`).
- Valores monetários em centavos, inteiro (`amount_cents`).
- Rotas com `Route::apiResource` em `routes/api.php`; route model binding.
- Status HTTP: 200, 201, 204, 401, 404, 409, 422.
- Testes: feature por endpoint (Pest, factories, `RefreshDatabase`) e
  unitários para Actions com regra.

### Frontend (`frontend/src/`)

```
api/          chamadas HTTP; nenhum componente usa fetch direto
hooks/        useExpenses, useCategories: estado e busca de dados
components/   componentes de UI reutilizáveis
features/     telas por funcionalidade (expenses, categories, auth)
utils/        formatação de moeda e datas
```

- Formulários controlados; erros 422 da API exibidos ao lado do campo.
- Nenhum componente mistura muitas responsabilidades.
- URL da API em `VITE_API_URL`; `.env` não vai para o Git.

## Comandos úteis

```sh
# backend
cd backend && php artisan serve
cd backend && php artisan test
# frontend
cd frontend && npm run dev
```

O PHP, o Composer e o `laravel` estão em `~/.config/herd-lite/bin`.
