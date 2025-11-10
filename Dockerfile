# Use PHP with Apache
FROM php:8.2-apache

# Install system dependencies and Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install Composer dependencies (use --no-dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy the rest of the application
COPY . /var/www/html/

# Set permissions for writable directory
RUN chmod -R 777 /var/www/html/writable

# Enable Apache rewrite module
RUN a2enmod rewrite

# Update Apache ports configuration for Render
RUN echo "Listen 10000" > /etc/apache2/ports.conf
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set the working directory to public
WORKDIR /var/www/html/public

# Expose Render's required port
EXPOSE 10000

# Start Apache
CMD ["apache2ctl", "-D", "FOREGROUND"]