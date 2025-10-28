FROM php:8.1-apache

WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --optimize-autoloader

# Copy the rest of the application
COPY . .

# Set Apache to serve from public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ensure data directory exists and is writable
RUN mkdir -p data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
