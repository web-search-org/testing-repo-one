FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    nodejs \
    npm

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    xml \
    zip \
    bcmath \
    pcntl \
    opcache

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install pnpm
RUN npm install -g pnpm

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
