# Use PHP with Apache
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy the app into the container
COPY . /var/www/html/

# Set permissions for writable directory
RUN chmod -R 777 /var/www/html/writable

# Enable Apache rewrite module
RUN a2enmod rewrite

# Update Apache ports configuration
RUN echo "Listen 10000" > /etc/apache2/ports.conf
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set the working directory
WORKDIR /var/www/html/public

# Expose Render's required port
EXPOSE 10000

# Start Apache on the correct port
CMD ["apache2ctl", "-D", "FOREGROUND"]