# 1. Escolhe a imagem base
FROM serversideup/php:8.2-fpm-nginx

# 2. Vira Root
USER root

# 3. Instala dependências do sistema
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 4. Instala Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 5. Instala extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql bcmath intl opcache

# 6. Define diretório e copia arquivos
WORKDIR /var/www/html
COPY . .

# 7. Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. NPM Build
RUN npm install && npm run build

# 9. Passa a posse dos arquivos para o usuário padrão
RUN chown -R 9999:9999 /var/www/html

# --- SCRIPT DE CORREÇÃO (ATUALIZADO) ---
# Aqui usamos 'printf' para criar um script robusto.
# 1. Cria as pastas de cache do Laravel se não existirem (essencial para volumes novos)
# 2. Aplica chmod 777 para garantir que o PHP consiga escrever, não importa o dono do volume.
RUN printf "#!/bin/sh\n\
mkdir -p /var/www/html/storage/framework/sessions\n\
mkdir -p /var/www/html/storage/framework/views\n\
mkdir -p /var/www/html/storage/framework/cache\n\
mkdir -p /var/www/html/storage/logs\n\
mkdir -p /var/www/html/bootstrap/cache\n\
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache\n\
exec \"\$@\"\n" > /etc/entrypoint.d/99-fix-perms.sh && \
chmod +x /etc/entrypoint.d/99-fix-perms.sh