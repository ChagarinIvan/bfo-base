# Build the SPA with the same Node/npm generation as package-lock.json.
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts --no-audit --no-fund

COPY resources/spa ./resources/spa
COPY resources/lang/by.json ./resources/lang/by.json
COPY vite.config.ts tsconfig.json ./
RUN npm run build:spa

FROM php:8.5-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl intl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
#RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
#RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
#RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Keep the generated SPA artifact from the frontend build stage.
COPY --from=frontend --chown=www:www /app/public/spa /var/www/public/spa

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
