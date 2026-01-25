# 1. Escolhe a imagem base
FROM serversideup/php:8.2-fpm-nginx

# 2. Vira Root
USER root

# 3. Instala dependências
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

# 5. Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql bcmath intl opcache

# 6. Copia arquivos
WORKDIR /var/www/html
COPY . .

# 7. Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. NPM Build
RUN npm install && npm run build

# 9. Ajusta permissões do Build
RUN chown -R 9999:9999 /var/www/html

# --- O PULO DO GATO (NOVO) ---
# Criamos um script que roda TODA VEZ que o container inicia.
# Ele força a pasta storage (que é um volume) a ser do usuário 9999.
RUN echo '#!/bin/sh' > /etc/entrypoint.d/99-fix-perms.sh && \
    echo 'chown -R 9999:9999 /var/www/html/storage /var/www/html/bootstrap/cache' >> /etc/entrypoint.d/99-fix-perms.sh && \
    echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache' >> /etc/entrypoint.d/99-fix-perms.sh && \
    chmod +x /etc/entrypoint.d/99-fix-perms.sh