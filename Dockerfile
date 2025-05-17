# Use official PHP-Apache image
FROM php:8.1-apache

# Enable Apache rewrite module (optional)
RUN a2enmod rewrite

# Install required PHP extensions, including PostgreSQL
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install curl pgsql pdo_pgsql

# Copy all project files to Apache root
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
