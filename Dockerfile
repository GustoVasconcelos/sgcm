# 1. Escolhe a imagem base (PHP 8.2 + Nginx prontos para Laravel)
FROM serversideup/php:8.2-fpm-nginx

# 2. Vira Root para instalar pacotes do sistema
USER root

# 3. Instala dependências do sistema
# (Zip/Unzip para o Composer + Libs gráficas para o DomPDF)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 4. Instala Node.js e NPM (Para compilar os assets do Front-end)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 5. Instala extensões do PHP necessárias (GD é vital para o DomPDF)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql bcmath intl opcache

# 6. Copia os arquivos do seu projeto para a imagem
COPY . /var/www/html

# 7. Define o dono dos arquivos (importante para evitar erro de permissão)
# O usuário padrão dessa imagem é 'webuser' (ID 9999)
RUN chown -R 9999:9999 /var/www/html

# 8. Troca para o usuário comum para rodar comandos de build
USER 9999

# 9. Instala dependências do PHP (Composer)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 10. Instala dependências do JS e compila (Vite/Mix)
RUN npm install && npm run build