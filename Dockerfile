# Use official PHP-Apache image
FROM php:8.1-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install curl pgsql pdo_pgsql

# Copy project files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Create persistent uploads directory and symbolic link
RUN mkdir -p /opt/render/project/uploads && \
    ln -sf /opt/render/project/uploads /var/www/html/uploads

EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

