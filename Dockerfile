FROM dptsi/laravel-web-dev:7.3

# Copy source code
COPY src /var/www/html/
RUN mkdir storage && mkdir storage/cache && mkdir storage/framework && mkdir storage/framework/sessions && mkdir storage/framework/views && mkdir storage/framework/cache && mkdir storage/logs && chmod -R ugo+rw storage/
# Install required packages
RUN composer require illuminate/redis:* --with-all-dependencies
