# # Use PHP 8.2 with Apache
# FROM php:8.2-apache

# # --------------------------
# # Install system dependencies
# # --------------------------
# RUN apt-get update && apt-get install -y \
#     git \
#     unzip \
#     curl \
#     libzip-dev \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     libonig-dev \
#     libxml2-dev \
#     libicu-dev \
#     && rm -rf /var/lib/apt/lists/*

# # --------------------------
# # Install PHP extensions (including intl)
# # --------------------------
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
#     && docker-php-ext-install \
#        pdo \
#        pdo_mysql \
#        mysqli \
#        zip \
#        gd \
#        mbstring \
#        exif \
#        intl

# # --------------------------
# # Install Composer
# # --------------------------
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # --------------------------
# # Set working directory
# # --------------------------
# WORKDIR /var/www/html

# # --------------------------
# # Copy Composer files first (for caching)
# # --------------------------
# COPY composer.json composer.lock ./

# # --------------------------
# # Install dependencies
# # --------------------------
# RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# # --------------------------
# # Copy the rest of the application
# # --------------------------
# COPY . .

# # --------------------------
# # Prepare writable directories and SQLite file
# # --------------------------
# RUN mkdir -p writable && touch writable/database.sqlite && chmod -R 777 writable

# # --------------------------
# # Enable Apache rewrite module
# # --------------------------
# RUN a2enmod rewrite

# # --------------------------
# # Configure Apache for Render
# # --------------------------
# COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf
# RUN echo "Listen 10000" > /etc/apache2/ports.conf

# # --------------------------
# # Expose port & set working directory
# # --------------------------
# WORKDIR /var/www/html/public
# EXPOSE 10000

# # --------------------------
# # Enable PHP error reporting for debugging
# # --------------------------
# RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
# RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
# RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini

# # --------------------------
# # Create simple test files
# # --------------------------
# RUN echo "<?php echo 'BASIC PHP WORKS!'; ?>" > /var/www/html/public/test1.php
# RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/test2.php

# # --------------------------
# # Start Apache
# # --------------------------
# CMD ["apache2ctl", "-D", "FOREGROUND"]


# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libonig-dev libxml2-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd mbstring exif intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy application
COPY . .

# Create .env if missing
RUN if [ ! -f ".env" ]; then \
    echo "CI_ENVIRONMENT = production" > .env; \
    echo "app.baseURL = 'https://hr-mini-app-kd2x.onrender.com'" >> .env; \
    fi

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 777 writable/

# Enable Apache rewrite
RUN a2enmod rewrite

# Apache config
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN echo "Listen 10000" > /etc/apache2/ports.conf

# Enable PHP errors
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

# TEST FILES - REMOVE THESE AFTER DEBUGGING
RUN echo "<?php echo 'TEST 1: Basic PHP works!'; ?>" > /var/www/html/public/test1.php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/test2.php
RUN echo "<?php require_once '../vendor/autoload.php'; echo 'Autoload OK!'; ?>" > /var/www/html/public/test3.php

WORKDIR /var/www/html/public
EXPOSE 10000
CMD ["apache2ctl", "-D", "FOREGROUND"]