FROM php:8.2-apache

# 1. Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 3. Configure Apache DocumentRoot and AllowOverride
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set working directory
WORKDIR /var/www/html

# 6. Copy only composer files first (for caching)
COPY composer.json composer.lock ./

# 7. Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 8. Copy the rest of the application
COPY . .

# 9. Create cache directory for Blade and set permissions
RUN mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/cache

# 10. Expose port 80
EXPOSE 80

# 11. Start Apache
CMD ["apache2-foreground"]
