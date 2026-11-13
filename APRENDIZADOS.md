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

## Etapa 10

1. Criei o ExpenseForm, formulário controlado: o valor de cada input fica num useState
2. O mesmo formulário serve pra criar e pra editar, muda só os valores iniciais
3. O select de categoria é carregado da API com um hook useCategories
4. O valor digitado em reais vira centavos antes de enviar (parseReais)
5. O hook useExpenses ganhou add, update e remove. Cada um chama a API e atualiza a lista local sem recarregar
6. Erro 422 da API aparece ao lado do campo. Botão fica desabilitado enquanto envia. Remover pede confirmação

Pergunta: por que validar no backend se o frontend também pode validar?
O frontend roda no navegador da pessoa, ela controla tudo ali. Dá pra pular a validação pelo DevTools ou chamando a API direto com curl. O backend é o único lugar que eu controlo, toda requisição passa por ele. Frontend valida pra dar resposta rápida, backend valida pra nada inválido entrar no banco.

## Etapa 11

1. Criei o ExpensesPage, a tela que guarda os filtros, chama os hooks e distribui tudo por props
2. ExpenseFilters com mês e categoria. Mudar qualquer um volta pra página 1
3. Summary mostra o total do mês e uma barra por categoria feita com CSS. A largura é a porcentagem calculada na hora, estado derivado
4. Pagination usa o meta que a API devolve, só aparece com mais de uma página
5. useExpenses recebe os filtros e o useEffect busca de novo quando eles mudam. Criar, editar ou remover recarrega a lista e o resumo
6. ExpenseList virou só apresentação, recebe tudo por props e não busca nada

Pergunta: quando vale a pena extrair um hook próprio?
Quando a lógica tem estado e efeito próprios e não é desenho de tela, ou quando dois componentes iam repetir o mesmo código. Buscar lançamentos tem lista, erro, carregando e useEffect. No hook o componente só pede os dados e desenha.

## Etapa 12

1. Adicionei o HasApiTokens no User e criei register, login, logout e /user no AuthController. A senha vira hash pelo cast do model e nunca sai no JSON
2. Migration nova adiciona user_id em categories e expenses, e troca o nome único global por único por usuário
3. Rotas protegidas num grupo com auth:sanctum. Sem token responde 401
4. Policies de Category e Expense: só o dono pode ver, editar e apagar, senão 403. Listar e criar partem de $request->user()->categories(), então já ficam presos ao dono
5. Validação: nome único só entre as categorias do usuário, e category_id tem que ser uma categoria dele
6. Atualizei factories, seeders (conta demo) e testes. 35 testes, 15 novos, incluindo o IsolationTest que prova que A não mexe nos dados de B
7. Tive que recriar o banco de dev com migrate:fresh --seed, porque os dados antigos não tinham dono

Erro que apareceu: no update, a validação rodava antes da autorização e dava 422 em vez de 403. Resolvi colocando a checagem no authorize() do Form Request, que roda antes das regras.

Pergunta: qual a diferença entre autenticação e autorização? Exemplo do Cofrinho.
Autenticação é saber quem está chamando: o token do Sanctum no cabeçalho, sem ele a API responde 401. Autorização é saber se essa pessoa pode mexer naquele dado: a Policy. Usuário A, logado, tenta apagar a categoria do B e recebe 403.

## Etapa 13

1. Criei o session.js, que guarda o token e o usuário no localStorage. Por isso a sessão sobrevive ao recarregar
2. O client.js põe Authorization: Bearer em toda chamada se tiver token. Se a API responder 401, avisa a sessão e volta pro login
3. AuthProvider é o estado global: começa com o que está no localStorage, confere o token na API ao abrir, e expõe login, register e logout pelo hook useAuth
4. Telas de login e cadastro usam o mesmo AuthForm, com erros 422 por campo. Botão Sair chama o logout da API e limpa a sessão
5. Testei: recarregar mantém logado, sair volta pro login, duas contas veem dados diferentes

Extras que faltavam: tela de categorias (criar e remover) com abas no App, e a Action RegisterUser que cria as 5 categorias padrão na conta nova. 36 testes passando.

Pergunta: o Sanctum recomenda cookies pra SPA própria, aqui usamos token. Quais os riscos e o que mudaria num produto real?
Token no localStorage é lido por qualquer JavaScript da página. Um script malicioso (XSS ou dependência comprometida) rouba o token e usa a conta de fora. O token também não expira sozinho. Num produto real a SPA usaria o modo cookie do Sanctum: sessão num cookie HttpOnly, que o JavaScript não lê, com proteção CSRF. Token ficaria pra app mobile e integrações, com expiração e escopo.

## Desafios opcionais

### CSV do mês

1. Rota GET /api/expenses/export?month=2026-11, declarada antes do apiResource senão "export" vira id de lançamento
2. Action ExportExpensesCsv monta o arquivo com fputcsv: separador ";" e decimal com vírgula, que o Excel em português abre direto. BOM no início pros acentos
3. Resposta com Content-Type text/csv e Content-Disposition attachment, o navegador entende como download
4. No front um link simples não serve porque o token vai no cabeçalho. O client.js ganhou requestBlob e o botão transforma o Blob em download

### Orçamento mensal por categoria

1. Coluna budget_cents opcional em categories, null é sem limite. Escolhi coluna em vez de tabela nova porque o limite é um só por categoria
2. O resumo devolve budget_cents e over_budget por categoria, calculado na mesma consulta do SUM
3. No front o formulário de categoria ganhou o campo de limite e passou a servir pra editar também
4. O Summary mostra gasto / limite e um alerta em vermelho com as categorias que passaram

### React Router

1. Instalei o react-router e troquei as abas por rotas: /, /categories, /login e /register
2. No App.jsx fica a tabela de rotas. Um grupo GuestOnly pra login e cadastro, um grupo RequireAuth pras páginas do app
3. RequireAuth: sem usuário faz Navigate pra /login. GuestOnly: logado em /login vai pra /
4. Layout tem o cabeçalho e o menu com NavLink, que marca a rota ativa sozinho, e o Outlet onde a página filha entra
5. Agora a URL muda, o botão voltar funciona e dá pra abrir /categories direto

### Cookies do Sanctum no lugar do token

1. No backend: statefulApi() no bootstrap, CORS com a origem exata do front e supports_credentials, e SANCTUM_STATEFUL_DOMAINS=localhost:5173 no .env
2. Login virou Auth::attempt + regenerate da sessão. Logout invalida a sessão. Nada de token na resposta
3. A sessão fica num cookie HttpOnly, que o JavaScript não lê. O que o JS lê é o cookie XSRF-TOKEN, e devolve ele no cabeçalho X-XSRF-TOKEN em POST, PUT e DELETE. Sem isso dá 419
4. No front: fetch com credentials include, GET /sanctum/csrf-cookie antes do primeiro POST, e se vier 419 pega um token novo e tenta de novo
5. Nos testes troquei Sanctum::actingAs por actingAs e coloquei um cabeçalho Origin padrão, senão a requisição não é "stateful" e não tem sessão. O teste de logout precisou de forgetGuards porque no teste os guards ficam em memória entre requisições
6. Validei com curl: login 200, rota protegida 200, POST sem CSRF 419, logout 204, depois 401

### TypeScript

1. Instalei o typescript, criei o tsconfig.json em modo strict e renomeei todo .js/.jsx pra .ts/.tsx. O Vite já entende TypeScript, o tsc só confere os tipos
2. Criei o src/types.ts com os formatos que a API devolve e recebe: User, Category, Expense, Summary, os inputs e os filtros
3. O request<T> do client diz qual JSON espera, então quem chama listCategories() já recebe Category[] sem adivinhar
4. Props de cada componente viraram uma interface. Erro que o tipo pegou: o AuthForm genérico não aceitava LoginInput porque interface não tem assinatura de índice; resolvi com a restrição { [K in keyof T]: string }
5. O build agora roda tsc antes do vite build, então erro de tipo quebra o build

## Etapa 14

1. Instalei o Docker Desktop no Windows com o motor WSL 2 e liguei a integração com o Ubuntu. docker run hello-world funcionou no terminal do WSL
2. Subi um Postgres 18 avulso com docker run: senha por variável de ambiente e -p 5432:5432 pra porta do container aparecer no meu computador. docker ps mostra o container e docker logs mostra o banco pronto
3. Criei uma tabela com psql dentro do container, removi o container com docker rm -f e subi outro igual: a tabela sumiu
4. Repeti com -v cofrinho-pg:/var/lib/postgresql. Removi e subi de novo com o mesmo volume: a tabela continuou lá
5. Descobri que a imagem já declara /var/lib/postgresql como volume, então mesmo sem -v o Docker cria um volume anônimo. Só que cada container novo ganha outro volume anônimo, por isso o dado parece sumir. Dar nome ao volume é o que faz o container novo reencontrar o dado
6. No Postgres 18 o caminho é /var/lib/postgresql, não mais /var/lib/postgresql/data. Montar no caminho antigo não guarda nada
7. Container não é máquina virtual: ele compartilha o kernel do sistema e só isola processos, por isso sobe em segundos. Não instalei cliente gráfico, conferi a porta 5432 aberta de fora do container e o pgAdmin da Etapa 15 vai cobrir essa parte

Pergunta: qual a diferença entre imagem e container?
Imagem é o pacote pronto e só leitura, com o programa e tudo que ele precisa, baixado do Docker Hub. Container é a imagem rodando, com disco próprio e descartável. Da mesma imagem saem quantos containers eu quiser.

Pergunta: por que a tabela sumiu no passo 4?
Porque o dado ficava no disco do container, e docker rm -f apaga o container inteiro. O container novo nasce da imagem, limpo. Com volume nomeado o dado fica fora do container e sobrevive.

## Etapa 15

1. Escrevi o backend/Dockerfile a partir da php:8.4-cli: instala a extensão pdo_pgsql, copia o Composer da imagem oficial, cria um usuário com o meu UID pra os arquivos do volume não ficarem de root, copia composer.json e composer.lock antes do resto do código e roda o composer install. Assim a camada das dependências fica em cache e só é refeita quando elas mudam
2. Criei o compose.yaml na raiz com três serviços: app (a imagem do Dockerfile, porta 8000), db (postgres:18 com healthcheck e volume pgdata) e pgadmin (porta 5050 com volume pgadmindata). O app só sobe depois que o pg_isready do db responde
3. No .env troquei o SQLite por pgsql com DB_HOST=db e o banco, usuário e senha iguais aos do Compose. O .env.example ficou igual, com um comentário de como voltar pro SQLite sem Docker
4. docker compose up --build subiu tudo, docker compose exec app php artisan migrate --seed criou as 8 tabelas no Postgres e a conta demo. O frontend não mudou nada, continua chamando localhost:8000
5. Testes: o phpunit.xml usa SQLite em memória. Decidi manter, porque é rápido e não depende do banco estar de pé. Rodei dentro do container e os 41 passaram
6. Provei os volumes: docker compose down e up mantém os dados; docker compose down -v apaga o pgdata e o banco volta vazio, zero tabelas
7. No pgAdmin cadastrei o servidor com host db, vi a tabela migrations, as colunas de expenses e a chave estrangeira pra categories, e um lançamento criado no React apareceu na hora. Um SELECT com SUM e GROUP BY no Query Tool deu o mesmo total que o /api/summary
8. README reescrito: o caminho principal agora é docker compose up, com uma seção pra rodar sem Docker

Pergunta: por que DB_HOST é db e não localhost?
Cada container tem a própria rede. Dentro do container do app, localhost é o próprio app, não tem Postgres lá. O Compose cria uma rede interna onde cada serviço é encontrado pelo nome, então db é o endereço do container do Postgres.

Pergunta: no pgAdmin o host é db, mas no cliente da Etapa 14 era localhost. Por quê?
O pgAdmin roda dentro de um container, na mesma rede interna do Compose, então chega ao banco pelo nome db. Um cliente instalado no Windows está fora dessa rede e só enxerga o banco pela porta publicada com -p no meu computador, que é o localhost.

Pergunta: qual a diferença entre o volume ./backend e o volume pgdata?
O ./backend é um bind mount: uma pasta do meu computador aparece dentro do container, então edito o código aqui e o container vê na hora. É por isso que o vendor da imagem fica escondido e o composer install precisa rodar de novo na primeira vez. O pgdata é um volume nomeado, gerenciado pelo Docker: os dados do Postgres ficam lá, fora do container, e sobrevivem ao down. Só o down -v apaga.

## Etapa 16

1. Adicionei o serviço redis no compose.yaml, imagem redis:8, com healthcheck de redis-cli ping. No .env troquei CACHE_STORE=database por redis e REDIS_HOST=redis
2. Cliente: escolhi o predis, que é só um pacote do Composer e funciona em qualquer lugar. A extensão PhpRedis é mais rápida, mas teria que compilar no Dockerfile, e aqui a diferença não importa
3. O SummarizeMonth ficou dentro de um Cache::remember com a chave summary:idDoUsuario:mes e 10 minutos de TTL. A primeira chamada roda o SUM no Postgres e guarda; as seguintes devolvem o que está no Redis
4. Invalidação: criei um ExpenseObserver ligado ao model com #[ObservedBy]. Ao salvar ou apagar um lançamento ele apaga a chave daquele mês. Se a edição mudou a data de mês, apaga os dois meses
5. Pra enxergar, liguei um DB::listen no AppServiceProvider que grava cada SQL no log quando DB_LOG_QUERIES=true. Primeira chamada do resumo: 1 consulta com SUM. Segunda: zero. As consultas que sobraram são da sessão, que continua no Postgres porque SESSION_DRIVER=database
6. No redis-cli -n 1 vi a chave com prefixo do Laravel e o TTL em 599 segundos. Depois de um POST de lançamento a chave sumiu e o resumo veio atualizado
7. Testes novos no SummaryCacheTest: cache guardado, atualização ao criar, editar e remover, e duas contas com resumos separados. 46 testes

Pergunta: o que aconteceria se a chave do cache não tivesse o id do usuário?
O primeiro usuário a pedir o resumo do mês gravaria o dele no Redis, e todos os outros receberiam esse mesmo resumo por 10 minutos. Vazamento de dados entre contas. Com o id na chave cada um tem a sua.

Pergunta: o que acontece com o Cofrinho se o container do Redis parar?
O resumo quebra, porque o Cache::remember tenta falar com o Redis e dá erro de conexão. O cache virou dependência. Num produto real o Redis teria redundância ou o código trataria a falha e iria direto ao banco.

## Etapa 17

1. QUEUE_CONNECTION=redis no .env. Criei a tabela exports (user_id, month, status, path, error) e o model Export com um enum ExportStatus: pending, done ou failed
2. POST /api/exports cria a linha como pending, despacha o job ExportExpensesCsv e responde 202 na hora. GET /api/exports/{id} devolve o status e, quando pronta, o link de download
3. O job reaproveita a Action que já gerava o CSV, salva o arquivo no storage e muda o status pra done. Tem tries 3 e backoff 5: três tentativas com 5 segundos entre elas. O método failed() só roda quando todas falharam, e marca failed com a mensagem do erro
4. No compose.yaml o serviço worker usa a mesma imagem do app, mas o comando é php artisan queue:work em vez do servidor. Os dois compartilham a pasta backend, por isso o app enxerga o CSV que o worker gravou
5. No React o botão Exportar mês chama o POST e um hook useExport consulta o status a cada 2 segundos. Quando vem done, baixa o arquivo; quando vem failed, mostra o erro
6. Experimentos: com sleep(5) no job e sem reiniciar o worker, continuou instantâneo. Reiniciei o worker e aí sim demorou 5 segundos. Com o worker parado, o pedido ficou pending e o job ficou esperando na lista do Redis; ao voltar, rodou. Com um throw forçado, o log mostrou 3 tentativas, a exportação ficou failed e apareceu uma linha em failed_jobs. Consertei o código, reiniciei o worker, queue:retry all, e ela terminou como done
7. Removi a rota síncrona antiga GET /expenses/export e o teste dela. 50 testes

Pergunta: por que não gerar o CSV dentro da requisição?
Porque a requisição fica presa até o arquivo ficar pronto. Com poucos lançamentos não importa, mas com milhares o navegador fica esperando, o servidor segura um processo e pode estourar o tempo limite. Na fila a resposta é imediata e o trabalho pesado acontece em outro processo, que pode até estar em outra máquina.

Pergunta: você mudou o código do job e o worker continuou com o comportamento antigo. Por quê?
O queue:work é um processo que fica rodando. Ele carrega o código uma vez quando sobe e não relê a cada job, diferente do artisan serve, que carrega a cada requisição. Por isso mudou o job, tem que reiniciar o worker. Em produção se usa queue:restart depois de cada deploy.

## Etapa 18

1. Criei as tabelas vaults (user_id, name, balance_cents) e vault_movements (vault_id, type, amount_cents), os models Vault e VaultMovement e o enum MovementType: deposit ou withdraw. Rotas: POST /api/vaults, GET /api/vaults/{id}, POST /api/vaults/{id}/deposit e withdraw. Saque maior que o saldo lança InsufficientBalanceException, que vira 422
2. O saque ingênuo faz quatro passos: lê o saldo, confere, calcula e salva. Pus um usleep de 0,3 segundo entre conferir e salvar pra alargar a janela do bug
3. O script scripts/race-withdraw.sh loga com cookie, cria um cofre com R$ 100, dispara 10 saques de R$ 80 ao mesmo tempo com xargs -P 10 e mostra o saldo e a soma das movimentações
4. Na primeira rodada o bug não apareceu, porque o artisan serve atende uma requisição por vez. Pus PHP_CLI_SERVER_WORKERS=10 no compose e continuou igual: o artisan serve ignora a variável sem a opção --no-reload. Com a opção no Dockerfile, 7 saques passaram, saldo final R$ 20 e soma das movimentações R$ -460. Saiu R$ 560 de um cofre de R$ 100: cada requisição leu 100, calculou 20 e salvou 20 por cima das outras
5. Correção 1, em commit próprio: DB::transaction com lockForUpdate na leitura do cofre. O SELECT ... FOR UPDATE tranca a linha até o COMMIT; as outras requisições param no SELECT e, quando leem, já veem o saldo descontado. 1 saque passou, 9 deram 422, saldo R$ 20 igual à soma. Testei também o lockForUpdate sem a transação: 10 passaram, saiu R$ 800, pior que a versão ingênua
6. Correção 2, em commit próprio: update atômico, UPDATE vaults SET balance_cents = balance_cents - 8000 WHERE id = ? AND balance_cents >= 8000. O decrement devolve quantas linhas mudaram; zero significa que faltou saldo. Tirei o usleep. Com 10 e com 20 saques, 1 passou e o saldo bateu
7. No pgAdmin rodei BEGIN e SELECT * FROM vaults WHERE id = 1 FOR UPDATE sem COMMIT, e o saque pela API ficou pendurado. Em pg_stat_activity minha sessão aparecia como idle in transaction e o UPDATE da API como active, esperando Lock transactionid, bloqueado pelo meu pid. Em pg_locks o pedido de lock da API estava com granted = f. Dei COMMIT e o saque respondeu 200 na hora, depois de 227 segundos esperando
8. Conferi no banco saldo contra soma das movimentações: nos cofres das versões com bug, 2000 contra -46000 e -70000; nos das corrigidas, 2000 contra 2000. 57 testes

Pergunta: desenhe uma linha do tempo com duas requisições mostrando onde o bug acontece.
t1 A lê saldo 100
t2 B lê saldo 100
t3 A confere 100 >= 80 e passa
t4 B confere 100 >= 80 e passa
t5 A salva saldo 20 e grava um saque de 80
t6 B salva saldo 20 e grava um saque de 80
O bug está entre t2 e t5: B leu antes de A salvar, então as duas trabalharam com um saldo que já não existia. No fim o saldo é 20, mas saíram 160.

Pergunta: por que lockForUpdate() fora de uma transação não resolve?
Porque o lock dura até o fim da transação. Sem BEGIN, cada comando é a própria transação, e o lock é solto assim que o SELECT termina, antes da conferência e do UPDATE. Na prática vira um SELECT comum. Testei: 10 saques passaram.

Pergunta: qual das duas correções você escolheria, e por quê?
O update atômico. É um comando só, não existe janela entre conferir e salvar, não preciso lembrar de trancar antes de ler, e o banco faz o lock sozinho. O lockForUpdate fica pra quando preciso ler vários dados e decidir no PHP antes de escrever, como uma regra que não cabe num WHERE.

## Etapa 19

1. Reproduzi com o script scripts/double-click.sh, que manda o mesmo POST /api/expenses duas vezes ao mesmo tempo. Sem chave: 201 e 201, dois lançamentos no banco
2. Migration: coluna idempotency_key na tabela expenses e índice único em (user_id, idempotency_key). Nulo não conta no índice, então requisição sem chave continua livre. A chave é por usuário, outro usuário pode usar a mesma
3. O StoreExpenseRequest lê o cabeçalho Idempotency-Key e coloca em idempotency_key nos dados validados, por cima de qualquer valor do corpo, com no máximo 64 caracteres. Criei a Action CreateExpense: tenta inserir direto e, se o banco recusar com UniqueConstraintViolationException, busca o lançamento que já tem aquela chave e devolve. O controller responde o mesmo 201 e, quando o lançamento não nasceu nessa requisição (wasRecentlyCreated falso), manda o cabeçalho Idempotent-Replayed: true
4. No React o ExpenseForm guarda um UUID num useRef, gerado com crypto.randomUUID na primeira tentativa. Deu certo, zera; falhou, o reenvio vai com a mesma chave. A chave passa pelo useExpenses e pelo createExpense, que manda o cabeçalho
5. Com a chave: 201 e 201 com Idempotent-Replayed, um lançamento só. O id 5 sumiu da tabela porque o insert recusado gastou um número da sequência do Postgres, e sequência não volta atrás
6. No job ExportExpensesCsv, Cache::lock("export:{user}:{mês}", 60)->block(15, ...): espera o lock, roda e solta no fim, mesmo com erro. Se não conseguir em 15 segundos, lança LockTimeoutException e o job tenta de novo pelas regras da Etapa 17. Os 60 segundos são a validade do lock, pra ele sumir sozinho se o worker morrer no meio
7. docker compose up -d --scale worker=2 e duas exportações do mesmo mês ao mesmo tempo, com um sleep(3) temporário no job: com o lock, a segunda só começou quando a primeira terminou, cada uma num container diferente; sem o lock compartilhado, as duas rodaram juntas
8. Testes: 7 de idempotência (sem chave cria dois, mesma chave cria um e repete o mesmo id, dados diferentes com a mesma chave devolvem o original, chaves diferentes, chave por usuário, chave longa dá 422, chave no corpo é ignorada) e 3 do lock (mesmo mês espera, meses diferentes não, lock solto depois de erro). 67 testes

Pergunta: por que o botão desabilitado da Etapa 10 não basta?
Porque ele só impede o segundo clique na tela. A requisição pode se repetir sem clique nenhum: a rede cai depois que o servidor gravou e o navegador ou o usuário tenta de novo, um proxy reenvia, duas abas abertas. A proteção tem que estar no servidor, que é quem grava.

Pergunta: por que conferir se a chave já existe antes de inserir também tem uma race condition?
Porque são dois comandos. As duas requisições conferem ao mesmo tempo, nenhuma acha a chave e as duas inserem. É a mesma janela do saque da Etapa 18. Com o índice único a conferência acontece dentro do próprio insert, e o banco garante que só um passa.

Pergunta: por que o lock fica no Redis, e não em uma variável do PHP?
Porque cada worker é um processo separado, aqui até um container separado, e uma variável só existe dentro do processo que a criou. O Redis é um lugar só que os dois enxergam. O mesmo vale quando a API roda em vários servidores.
