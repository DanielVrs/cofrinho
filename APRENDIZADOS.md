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

