FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create required directories
RUN mkdir -p /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage \
    && mkdir -p /var/www/html/storage/app \
    && mkdir -p /var/www/html/storage/framework \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chmod -R 755 /var/www/html/bootstrap \
    && chmod -R 755 /var/www/html/storage

# Copy application files
COPY jobportal-backend/ .

# Copy SSL certificate
COPY jobportal-backend/ca.pem /var/www/html/ca.pem

# Create .env file using environment variables (DB_PASSWORD comes from Render)
RUN echo "APP_ENV=production" > /var/www/html/.env && \
    echo "APP_DEBUG=false" >> /var/www/html/.env && \
    echo "APP_URL=https://job-portal-api-wifg.onrender.com" >> /var/www/html/.env && \
    echo "DB_CONNECTION=mysql" >> /var/www/html/.env && \
    echo "DB_HOST=${DB_HOST}" >> /var/www/html/.env && \
    echo "DB_PORT=${DB_PORT}" >> /var/www/html/.env && \
    echo "DB_DATABASE=${DB_DATABASE}" >> /var/www/html/.env && \
    echo "DB_USERNAME=${DB_USERNAME}" >> /var/www/html/.env && \
    echo "DB_PASSWORD=${DB_PASSWORD}" >> /var/www/html/.env && \
    echo "DB_SSL_CA=/var/www/html/ca.pem" >> /var/www/html/.env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod 644 /var/www/html/.env

# Generate application key
RUN php artisan key:generate

# Nginx configuration
RUN echo 'server { \
    listen 8080; \
    root /var/www/html/public; \
    index index.php; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/sites-enabled/default

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080"]