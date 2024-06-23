FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

COPY . /var/www/html/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Nginx
RUN apt-get update && apt-get install -y nginx

# Copy Nginx configuration file
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose ports
EXPOSE 80

COPY crontab /etc/cron.d/crontab

RUN chmod 0644 /etc/cron.d/crontab && \
    crontab /etc/cron.d/crontab

# Start PHP-FPM and Nginx
CMD service nginx start && php-fpm && cron -f
