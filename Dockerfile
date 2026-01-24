# 1. Escolhe a imagem base
FROM serversideup/php:8.2-fpm-nginx

# 2. Vira Root para instalar pacotes
USER root

# 3. Instala dependências do sistema
# ADICIONEI: libicu-dev
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

# 4. Instala Node.js e NPM
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 5. Instala extensões do PHP
# Agora o 'intl' vai funcionar porque instalamos o libicu-dev acima
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql bcmath intl opcache

# 6. Copia os arquivos do projeto
COPY . /var/www/html

# 7. Ajusta permissões (Owner: webuser = 9999)
RUN chown -R 9999:9999 /var/www/html

# 8. Troca para usuário comum
USER 9999

# 9. Instala dependências do Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 10. Compila o Front-end
RUN npm install && npm run build