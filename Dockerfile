FROM php:8.2-apache

# Configure Apache with an explicit absolute public document root.
RUN set -eux; \
    a2enmod rewrite; \
    docker-php-ext-install pdo_mysql; \
    { \
        echo '<VirtualHost *:80>'; \
        echo '    ServerAdmin webmaster@localhost'; \
        echo '    DocumentRoot /var/www/html/public'; \
        echo '    <Directory /var/www/html/public>'; \
        echo '        AllowOverride All'; \
        echo '        Require all granted'; \
        echo '    </Directory>'; \
        echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
        echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
        echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

EXPOSE 80
