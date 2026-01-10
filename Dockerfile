# -------------------------------
# PHP 8.2 with FPM (NO Apache)
# -------------------------------
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libonig-dev

# PHP extensions
RUN docker-php-ext-install zip

# Configure PHP-FPM to listen on socket
RUN mkdir -p /run/php && \
    sed -i 's/listen = 127.0.0.1:9000/listen = \/run\/php\/php-fpm.sock/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.mode = 0660/listen.mode = 0666/g' /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy frontend files
COPY . .

# Copy Nginx config
COPY ./nginx.conf /etc/nginx/sites-available/default

# Copy supervisor config
COPY ./supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Expose port
EXPOSE 80

# Start supervisor → Starts nginx + php-fpm
CMD ["/usr/bin/supervisord", "-n"]
