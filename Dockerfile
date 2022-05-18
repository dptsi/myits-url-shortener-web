FROM dptsi/laravel-web-dev:7.3

# Copy source code
COPY src /var/www/html/

# Install required packages
RUN composer install && composer require predis/predis

# change default uid
RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R www-data:www-data /var/www/html && \
  chown -R www-data:www-data /run && \
  chown -R www-data:www-data /var/run && \
  chown -R www-data:www-data /var/lib/nginx && \
  chown -R www-data:www-data /var/log/nginx

# Setup nginx pid
RUN touch /var/run/nginx.pid && \
      chown -R www-data:www-data /var/run/nginx.pid
