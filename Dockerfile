# Use PHP with Apache
FROM php:8.2-apache

# Install required extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy your app into the Apache web root
COPY . /var/www/html/

# Ensure writable folder permissions
RUN chmod -R 777 /var/www/html/writable

# Enable Apache rewrite (needed for CodeIgniter routes)
RUN a2enmod rewrite
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose Render's expected port
EXPOSE 10000

# Run Apache on the right port
CMD ["apache2ctl", "-D", "FOREGROUND"]
