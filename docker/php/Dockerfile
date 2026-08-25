FROM php:8.4-fpm-alpine

# Install system dependencies and build packages
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    imagemagick \
    imagemagick-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    freetype-dev \
    mysql-client \
    sudo \
    shadow \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mysqli \
        opcache \
        pdo_mysql \
        zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del --no-network $PHPIZE_DEPS \
    && rm -rf /tmp/pear

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Copy custom PHP configuration
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]

