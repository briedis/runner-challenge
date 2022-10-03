FROM php:8.0-fpm
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN usermod -u 1000 www-data
WORKDIR "/app"
