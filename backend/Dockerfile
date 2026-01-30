FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    librabbitmq-dev \
    libssl-dev \
    pkg-config \
    build-essential


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis \
    && docker-php-ext-install redis

# Install AMQP extension from source
RUN git clone https://github.com/php-amqp/php-amqp.git /usr/src/php/ext/amqp \
    && docker-php-ext-install amqp

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/loyalty loyalty
RUN mkdir -p /home/loyalty/.composer && \
    chown -R loyalty:loyalty /home/loyalty

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Copy existing application directory permissions
RUN chown -R loyalty:loyalty /var/www

COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Change current user to loyalty
USER loyalty

# Expose port 9000 and start php-fpm server
EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
