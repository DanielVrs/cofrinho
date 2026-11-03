# Aprendizados

Registro do que foi feito e aprendido em cada etapa do teste.

## Etapa 0: preparar o ambiente

**O que foi feito**

- Instalado PHP 8.4, Composer e o instalador do Laravel via php.new, sem sudo, em `~/.config/herd-lite/bin`.
- Node 22 e npm já estavam instalados.
- Backend criado com `laravel new backend`, sem starter kit, banco SQLite e Pest para testes. Laravel 13.
- Frontend criado com `npm create vite@latest frontend -- --template react`. React 19 e Vite 8.
- Os dois servidores subiram: Laravel em `http://localhost:8000` e React em `http://localhost:5173`.

**Pergunta: por que o arquivo `.env` não deve ir para o Git?**

O `.env` guarda a configuração da máquina onde o projeto roda: chave da aplicação (`APP_KEY`), caminho do banco, senhas e tokens de serviços externos. Dois motivos para não versionar:

1. Segurança: qualquer pessoa com acesso ao repositório veria senhas e chaves. Em repositório público, isso é vazamento.
2. Cada ambiente é diferente: minha máquina, a do colega e o servidor de produção usam valores diferentes. O que vai para o Git é o `.env.example`, só com os nomes das variáveis, e cada ambiente cria o seu `.env` a partir dele.

**Dúvidas e erros**

- Nenhum erro de instalação. O `sudo` pedia senha, então o PHP foi instalado na home do usuário em vez de via `apt`.
