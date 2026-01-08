FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create cache directory for Blade
RUN mkdir -p /var/www/html/cache

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache DocumentRoot to current directory (default /var/www/html is correct)
# But ensure AllowOverride is set for .htaccess if needed (though we use index.php router)
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set ServerName to suppress warning
RUN echo "ServerName buyorix.onrender.com" >> /etc/apache2/apache2.conf

EXPOSE 80
