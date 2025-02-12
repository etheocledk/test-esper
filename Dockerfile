FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring xml gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy .env file 
COPY .env.dev .env

# Copy application files and set correct permissions
COPY --chown=www-data:www-data . .

# Ensure Laravel can write to storage and cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]
