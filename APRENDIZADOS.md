# Aprendizados

Aqui vou anotando o que fiz e o que aprendi em cada etapa.

## Etapa 0

1. Instalei o PHP pelo php.new, porque o sudo pedia senha e não quis mexer nisso
2. Instalei o Composer, que é o npm do PHP
3. Instalei o instalador do Laravel
4. Criei o backend com laravel new backend. Fui sem starter kit porque as telas vão ser em React, SQLite por ser só um arquivo e Pest para os testes
5. Criei o front com npm create vite
6. Subi os 2 servers e abri no navegador, deu certo

Pergunta: por que o .env não vai para o Git?
Porque tem senha e chave dentro, e cada máquina usa valores diferentes. O que sobe é o .env.example, só com os nomes.


## Etapa 1

1. Criei o exercicios/gastos.php, roda com php exercicios/gastos.php
2. Montei um array com 5 gastos, cada um com descrição, categoria e valor
3. Fiz uma função que soma o total por categoria com foreach
4. Criei a classe Gasto com propriedades tipadas e o método valorFormatado()
5. Refiz o total usando objetos Gasto em vez de array
6. Guardei o valor em centavos (inteiro) para não ter problema de arredondamento com float. R$ 12,50 vira 1250

Pergunta: qual a diferença entre $gasto['valor'] e $gasto->valor?
Com colchetes estou lendo uma chave de array, com a seta estou lendo uma propriedade de um objeto. No array não tem garantia nenhuma, posso errar o nome da chave e ele só devolve null. No objeto a classe já define quais campos existem, o tipo de cada um e os métodos.


## Etapa 2

1. Rodei php artisan install:api, que criou o routes/api.php e instalou o Sanctum (vai servir para o login mais pra frente)
2. Rodei a migration que o Sanctum trouxe
3. Criei a rota GET /ping no routes/api.php devolvendo {"status": "ok"}
4. Conferi com php artisan route:list, a rota aparece como api/ping
5. Subi o server e chamei /api/ping, voltou 200 com o JSON

Pergunta: quem colocou o prefixo /api na rota?
O bootstrap/app.php. O install:api registrou o routes/api.php lá no parâmetro api, e tudo que está nesse arquivo ganha o prefixo /api automaticamente.

## Etapa 3

1. Rodei php artisan make:model Category -ms, gerou o model, a migration e o seeder
2. Na migration coloquei name (único) e color
3. No model só o $fillable com name e color, para poder criar com Category::create()
4. No CategorySeeder coloquei 5 categorias com firstOrCreate, assim o seed roda de novo sem duplicar
5. Chamei o CategorySeeder no DatabaseSeeder e rodei php artisan migrate --seed
6. No Tinker: Category::count() deu 5, criei uma categoria, apaguei, voltou pra 5

Pergunta: por que usar migrations em vez de criar as tabelas direto no banco?
Porque a migration é código e vai pro Git. Quem clonar o projeto roda migrate e tem o mesmo banco que eu. Criando direto no banco fica só na minha máquina e sem histórico de mudanças.

## Etapa 4

1. Gerei o CategoryController com --api, já vem com os 5 métodos
2. Criei os Form Requests com as regras de name e color
3. Criei o CategoryResource, que define o JSON de saída
4. Registrei as 5 rotas com Route::apiResource
5. O {category} da URL vira objeto sozinho (route model binding), id inexistente dá 404
6. Testei com curl: 200, 201, 204, 422 e 404

Pergunta: o que acontece desde a chegada de um POST até a resposta 201?
Entra pelo index.php, o bootstrap monta a app e acha a rota. Antes do controller roda o Form Request, se falhar volta 422. Passou, o store faz o Category::create, o Resource monta o JSON e volta 201.

## Etapa 5

1. Criei o model Expense com migration, a tabela tem category_id como chave estrangeira
2. No Expense coloquei belongsTo(Category) e no Category hasMany(Expense), os dois lados da relação
3. Cast de spent_on para date, assim vem como objeto de data e não string
4. CRUD de /api/expenses no mesmo padrão da etapa 4: Form Request, Resource, apiResource
5. A listagem usa with('category') pra trazer a categoria junto
6. Regra de negócio: categoria com lançamento não apaga. Ficou numa Action (DeleteCategory) que lança uma exceção, e o bootstrap transforma em 409

Pergunta: por que centavos em inteiro em vez de float?
Float arredonda mal, 0.1 + 0.2 não dá 0.3 exato. Com inteiro a conta é sempre exata.

Pergunta: o que é o problema N+1?
Uma consulta pra buscar a lista e mais uma por item pra buscar a relação. Com 100 lançamentos são 101 consultas. Testei: com with foram 2 consultas, sem with foram 6 pra 5 lançamentos.

## Etapa 6

1. Criei dois scopes no Expense, forMonth e forCategory, pedaços de consulta com nome
2. Form Request pra validar a query string (month no formato Y-m, category_id existente), também funciona em GET
3. No index usei when() pra aplicar cada filtro só se veio na URL, e paginate(15) no lugar do get()
4. Criei a Action SummarizeMonth, que faz SUM com GROUP BY e JOIN direto no banco
5. Rota GET /api/summary num controller invokable
6. Testei: filtros juntos e separados, paginação com meta, resumo, mês inválido 422

Pergunta: por que somar no banco em vez de carregar tudo e somar no PHP?
O banco já é feito pra isso. Somando no PHP eu trago todos os lançamentos pela rede e gasto memória pra devolver 5 números. No banco ele devolve só uma linha por categoria.

## Etapa 7

1. Criei as factories de Category e Expense, geram registros falsos pros testes
2. Liguei o RefreshDatabase no tests/Pest.php, vinha comentado
3. Os testes rodam num SQLite em memória (phpunit.xml), não mexem no meu banco
4. Escrevi 20 testes em 3 arquivos: categorias, lançamentos e resumo. Cobrem 201, 422, 409, 404, filtro por mês e categoria, paginação e resumo
5. php artisan test passa tudo em 0,3s
6. Quebrei a regra do 409 de propósito e o teste falhou. Deu 500 em vez de 204 porque a chave estrangeira no banco também impede apagar

Pergunta: o que o RefreshDatabase faz, e o que aconteceria sem ele?
Roda as migrations num banco limpo e desfaz tudo no fim de cada teste. Sem ele um teste veria os dados do outro: o de nome repetido ia depender da ordem e o de paginação ia contar lançamento dos outros.

## Etapa 8

1. Tirei a tela de demonstração do Vite e deixei o App.jsx só montando a página
2. Criei um array fixo de lançamentos no mesmo formato que a API devolve
3. ExpenseList guarda a lista num useState e faz o map com key={expense.id}
4. ExpenseItem recebe o lançamento por props e desenha uma linha com o botão Remover
5. Remover chama setExpenses com um array novo sem o item (filter), nunca mexe no array atual
6. formatCents(1250) devolve R$ 12,50 usando o Intl do navegador

Pergunta: por que alterar uma variável comum não atualiza a tela, mas chamar a função do useState sim?
O React não fica vigiando variável. Ele só redesenha o componente quando eu chamo a função do useState, que avisa que o estado mudou. Mudando uma variável comum o valor muda na memória mas ninguém avisa o React.

## Etapa 9

1. Criei o frontend/.env com VITE_API_URL=http://localhost:8000/api, e o .env.example que vai pro Git
2. Criei o src/api/client.js com a única função que faz fetch. Monta a URL, manda JSON e transforma erro em exceção
3. Criei o hook useExpenses, que busca os dados num useEffect quando o componente aparece
4. A lista agora mostra três estados antes dos dados: carregando, erro e vazia
5. Testei: com o backend ligado aparecem os lançamentos do banco, desligado aparece a mensagem de erro
6. Não deu erro de CORS porque o Laravel já libera api/* pra qualquer origem por padrão

Pergunta: o que é CORS, e por que funciona no Postman mas falha no navegador?
CORS é uma regra do navegador. A página em localhost:5173 chamando localhost:8000 é outra origem, e o navegador só entrega a resposta se o servidor mandar o cabeçalho Access-Control-Allow-Origin liberando. Postman e curl não são navegador, não aplicam essa regra.
