FROM dptsi/laravel-web-dev:7.3

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
    libmcrypt-dev

# Install GD extension with Alpine Linux-specific configuration
RUN docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-webp-dir=/usr/include/ \
    --with-xpm-dir=/usr/include/ \
    && docker-php-ext-install gd

# Install Imagick extension
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Copy source code into the container
COPY src /var/www/html/

# Create storage directories and set permissions
RUN mkdir -p \
    storage/cache \
    storage/framework \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    && chmod -R ugo+rw storage/

# Install required Composer packages
RUN composer require illuminate/redis:* --with-all-dependencies
