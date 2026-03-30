FROM php:8.2-apache

# Enable Apache rewrite for front controller routing and install PDO MySQL extension.
RUN a2enmod rewrite \
    && docker-php-ext-install pdo_mysql

# Allow .htaccess overrides in document root for URL rewriting.
RUN set -eux; \
    sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

EXPOSE 80
