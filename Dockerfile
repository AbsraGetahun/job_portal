FROM php:8.2-fpm 
RUN apt-get update && apt-get install -y git curl libpng-dev libonig-dev libxml2-dev zip unzip nginx 
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer 
WORKDIR /var/www/html 
COPY jobportal-backend/ . 
RUN composer install --no-dev --optimize-autoloader 
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html/storage && chmod -R 755 /var/www/html/bootstrap/cache 
COPY jobportal-backend/ca.pem /var/www/html/ca.pem 
RUN echo "'server { listen 8080; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; fastcgi_index index.php; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; } }'" 
EXPOSE 8080 
CMD ["sh", "-c", "php artisan migrate --force ^&^& php artisan serve --host=0.0.0.0 --port=8080"] 
