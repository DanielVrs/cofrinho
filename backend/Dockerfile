# Imagem base: PHP 8.4 com o executável de linha de comando (serve pro artisan serve)
FROM php:8.4-cli

# Extensão pdo_pgsql, para o PHP falar com o Postgres.
# libpq-dev é a biblioteca C do Postgres que a extensão precisa pra compilar.
# git e unzip são usados pelo Composer para baixar pacotes.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev git unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Composer copiado da imagem oficial, em vez de instalar na mão
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Usuário com o mesmo UID da máquina (1000). Assim os arquivos que o container
# cria na pasta compartilhada (logs, cache, vendor) não ficam de root.
ARG UID=1000
RUN useradd --uid ${UID} --create-home app
USER app

WORKDIR /var/www/html

# Dependências primeiro: enquanto composer.json e composer.lock não mudarem,
# esta camada fica em cache e o build não baixa tudo de novo.
COPY --chown=app:app composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

# Agora o código. Muda a toda hora, por isso vem por último.
COPY --chown=app:app . .
RUN composer dump-autoload --optimize

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
