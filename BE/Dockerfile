FROM php:8.2-apache

WORKDIR /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data


# Copy custom apache2 config
COPY docker/apache/httpd.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/apache/ports.conf /etc/apache2/ports.conf

# Install important libraries/packages
RUN apt-get update -y && apt-get install -y \
    zip unzip \
    libzip-dev \
    supervisor default-mysql-client \
    && docker-php-ext-install zip pdo_mysql \
    && a2enmod rewrite


# Make sure www-data user can access apache2 logs
RUN mkdir -p /var/log/apache2 && \
    chown -R www-data:www-data /var/log/apache2 && \
    chmod -R 755 /var/log/apache2

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY . .

# Make sure ownership is correct for copied files in frontend
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Create log directories that are needed by supervisor
RUN mkdir /var/log/supervisor/apache2 && \
    mkdir /var/log/supervisor/app-schedule && \
    chown www-data:www-data -R /var/log/supervisor && \
    chmod 755 -R /var/log/supervisor



# Entrypoint that does composer install and starts supervisor
EXPOSE 8000
RUN chmod +x "docker/entrypoint.sh"
ENTRYPOINT ["sh", "docker/entrypoint.sh"]
