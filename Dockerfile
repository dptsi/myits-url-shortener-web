FROM dptsi/laravel-web-dev:8.5

# Install required system libraries and tools
RUN apk --no-cache add \
    imagemagick \
    imagemagick-dev \
    libtool \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    libltdl \
    tmux \
    bash \
    curl \
    busybox-suid \
    $PHPIZE_DEPS

# Rebuild GD with FreeType/JPEG/WebP/XPM support (PHP >= 7.4 flag syntax)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    && docker-php-ext-install gd

# Install Imagick extension (dipakai simple-qrcode untuk format PNG)
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Copy source code into the container
COPY src /var/www/html/

# Create storage directories and set permissions
RUN mkdir -p \
    storage/app \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && chmod -R ugo+rw storage/ bootstrap/cache/

# Install Composer packages
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Jalankan skrip saat container dimulai
RUN chmod +x /var/www/html/hit_api.sh

# ─── Cron: generate QR code otomatis (dalam tmux) ─────────────────────────────
# Pasang crontab untuk busybox crond
COPY qr-crontab /etc/crontabs/root
# Daftarkan crond sebagai program supervisor — append ke config aktif yang
# dipakai CMD (/etc/supervisor/conf.d/supervisord.conf), karena config tsb
# tidak punya [include] ke /etc/supervisor.d/
COPY supervisor-crond.ini /tmp/supervisor-crond.ini
RUN cat /tmp/supervisor-crond.ini >> /etc/supervisor/conf.d/supervisord.conf \
    && rm /tmp/supervisor-crond.ini
# Pastikan script generate & wrapper cron bisa dieksekusi
RUN chmod +x /var/www/html/bulk-generate-qrcode-curl.sh /var/www/html/qr-cron.sh
#CMD ["/var/www/html/hit_api.sh"]
