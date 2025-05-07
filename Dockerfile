# Use official PHP-Apache image
FROM php:8.1-apache

# Enable Apache rewrite module (optional, for clean URLs)
RUN a2enmod rewrite

# Install cURL for Telegram API and other useful extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    zip \
    unzip \
    && docker-php-ext-install curl

# Copy all project files to Apache's root directory
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80

# Use Apache foreground to start
CMD ["apache2-foreground"]
