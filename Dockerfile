FROM php:8.2-apache

# Enable mod_rewrite na mod_headers
RUN a2enmod rewrite headers

# Install extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Copy files
COPY . /var/www/html/

# Apache config - ruhusu .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
