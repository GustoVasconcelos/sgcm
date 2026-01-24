# 1. Escolhe a imagem base
FROM serversideup/php:8.2-fpm-nginx

# 2. Vira Root para instalar tudo sem bloqueios
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

# 4. Instala Node.js e NPM
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 5. Instala extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql bcmath intl opcache

# 6. Define diretório e Copia os arquivos
WORKDIR /var/www/html
COPY . .

# --- MUDANÇA ESTRATÉGICA AQUI ---
# Rodamos as instalações como ROOT para evitar erro de permissão na pasta cache

# Permite composer rodar como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# 7. Instala dependências do PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. Instala dependências do JS e compila (Agora o npm tem poder de root)
RUN npm install && npm run build

# 9. O PASSO FINAL IMPORTANTE:
# Agora que tudo foi criado, passamos a posse dos arquivos para o usuário 'webuser' (9999)
# para que o servidor web consiga ler e escrever quando o site estiver no ar.
RUN chown -R 9999:9999 /var/www/html

# 10. Troca para usuário comum apenas para rodar a aplicação (Segurança)
USER 9999