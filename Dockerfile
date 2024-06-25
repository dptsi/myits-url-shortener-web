FROM dptsi/laravel-web-dev:7.3

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
    libmcrypt-dev

# Install extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm && \
    docker-php-ext-install gd

RUN pecl install imagick && docker-php-ext-enable imagick
# Copy source code
COPY src /var/www/html/
RUN mkdir storage && mkdir storage/cache && mkdir storage/framework && mkdir storage/framework/sessions && mkdir storage/framework/views && mkdir storage/framework/cache && mkdir storage/logs && chmod -R ugo+rw storage/
# Install required packages
RUN composer require illuminate/redis:* --with-all-dependencies
