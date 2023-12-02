FROM php:8.2-apache


#
#RUN touch /var/log/cron.log

RUN adduser newsagg --uid 1000


WORKDIR /var/www/html

COPY ./ /var/www/html
COPY docker/apache/httpd.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/apache/ports.conf /etc/apache2/ports.conf

RUN apt-get update && apt-get install bash

RUN apt-get update && \
    apt-get install -y libzip-dev zip && \
    apt-get install -y default-mysql-client  && \
    apt-get install -y supervisor cron   && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install zip && \
    a2enmod rewrite

RUN chmod 755 /var/run && \
    mkdir -p /var/log/supervisor && \
    chmod -R 755 /var/log/supervisor && \
    chown -R newsagg:newsagg /var/log/supervisor


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev

#RUN echo "* * * * * www-data php /var/www/html/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf



RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache


RUN php artisan storage:link
RUN php artisan optimize:clear



# Create an empty supervisord.log file
RUN touch /var/log/supervisor/supervisord.log && \
    chown newsagg:newsagg /var/log/supervisor/supervisord.log && \
    chmod 755 /var/log/supervisor/supervisord.log


RUN mkdir /var/log/supervisor/apache2 && \
    mkdir /var/log/supervisor/app-schedule

    # Create an empty supervisord.log file
RUN chown newsagg:newsagg -R /var/log/supervisor && \
    chmod 755 -R /var/log/supervisor


#CMD cron -f && apache2ctl -D FOREGROUND
# Start supervisord
RUN echo "Starting!"
CMD supervisord -c /etc/supervisor/conf.d/supervisord.conf
EXPOSE 8000
