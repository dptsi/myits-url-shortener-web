FROM dptsi/laravel-web-dev:7.3

# Copy source code
COPY src /var/www/html/

# Install required packages
RUN composer install && composer require illuminate/redis:* --with-all-dependencies

USER root
