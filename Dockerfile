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
# # Install PHP extensions
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
# # Install Composer globally
# # --------------------------
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # --------------------------
# # Set working directory
# # --------------------------
# WORKDIR /var/www/html

# # --------------------------
# # Copy composer files first
# # --------------------------
# COPY composer.json composer.lock ./

# # --------------------------
# # Debug: Check files & PHP extensions
# # --------------------------
# RUN ls -la
# RUN php -v
# RUN php -m

# # --------------------------
# # Install Composer dependencies
# # --------------------------
# RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress -vvv

# # --------------------------
# # Copy the rest of the application
# # --------------------------
# COPY . .

# # --------------------------
# # Set permissions for writable directories
# # --------------------------
# RUN chmod -R 777 writable

# # --------------------------
# # Enable Apache rewrite module
# # --------------------------
# RUN a2enmod rewrite

# # --------------------------
# # Configure Apache ports for Render
# # --------------------------
# RUN echo "Listen 10000" > /etc/apache2/ports.conf
# COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# # --------------------------
# # Set default working directory to public
# # --------------------------
# WORKDIR /var/www/html/public

# # Expose port
# EXPOSE 10000

# # Start Apache in foreground
# CMD ["apache2ctl", "-D", "FOREGROUND"]



# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install required dependencies
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

# Copy composer files first and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy application files
COPY . .

# Ensure SQLite file exists and is writable
RUN mkdir -p writable && touch writable/database.sqlite && chmod -R 777 writable

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache to use custom port and settings
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN echo "Listen 10000" > /etc/apache2/ports.conf

# Expose port and run Apache
EXPOSE 10000
WORKDIR /var/www/html/public
CMD ["apache2ctl", "-D", "FOREGROUND"]
