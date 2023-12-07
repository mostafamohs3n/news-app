#!/bin/bash
# Run composer install
composer install --no-interaction --verbose

# Run supervisor that starts apache2 and the laravel scheduler
supervisord -c /etc/supervisor/conf.d/supervisord.conf
