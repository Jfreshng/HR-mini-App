# Use PHP with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd mbstring exif

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock ./

# Debug: Show what files we have
RUN ls -la

# Debug: Show PHP version and extensions
RUN php -v
RUN php -m

# Install Composer dependencies with verbose output
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress -vvv

# Copy the rest of the application
COPY . .

# Set permissions for writable directory
RUN chmod -R 777 writable

# Enable Apache rewrite module
RUN a2enmod rewrite

# Update Apache ports configuration for Render
RUN echo "Listen 10000" > /etc/apache2/ports.conf
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/public
EXPOSE 10000
CMD ["apache2ctl", "-D", "FOREGROUND"]