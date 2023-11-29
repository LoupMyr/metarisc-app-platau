# syntax=docker/dockerfile:1.4

FROM php:8.1-apache-bullseye

# PHP Extensions
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install sockets
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo_mysql

# Zip (Composer downloader)
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev && apt-get clean && rm -rf /var/lib/apt/lists/* && docker-php-ext-install zip

# Apache2 configuration
ENV PORT 80
COPY config/apache2.conf /etc/apache2/sites-available/000-default.conf 
RUN a2enmod rewrite
RUN (echo "ServerName localhost" | tee /etc/apache2/conf-available/servername.conf) && a2enconf servername
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf

USER www-data

# Base dir
WORKDIR /var/www/html

# Install dependencies (prod mode)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install -n --no-dev --no-progress --no-scripts --ignore-platform-reqs

# Copy sources files
COPY cli-config.php cli-config.php
COPY public public
COPY config config
COPY src src
COPY templates templates
COPY .env .env

# Insert application
COPY boot.sh boot.sh
CMD ["bash", "/var/www/html/boot.sh"]