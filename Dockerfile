FROM php:8.2-apache

# Install dependencies for GD with WebP/AVIF support
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libavif-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install GD with WebP, AVIF, JPEG, PNG, Freetype support
RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-webp \
    --with-avif \
    --with-freetype \
    && docker-php-ext-install -j$(nproc) gd

# Install PDO MySQL
RUN docker-php-ext-install pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers deflate expires

# Configure Apache virtual host
RUN { \
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

# Configure compression and caching
RUN { \
    echo '<IfModule mod_deflate.c>'; \
    echo '    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json image/svg+xml'; \
    echo '</IfModule>'; \
    echo ''; \
    echo '<IfModule mod_expires.c>'; \
    echo '    ExpiresActive On'; \
    echo '    ExpiresByType image/webp "access plus 1 year"'; \
    echo '    ExpiresByType image/avif "access plus 1 year"'; \
    echo '    ExpiresByType image/jpeg "access plus 1 year"'; \
    echo '    ExpiresByType image/png "access plus 1 year"'; \
    echo '    ExpiresByType image/gif "access plus 1 year"'; \
    echo '    ExpiresByType text/css "access plus 1 month"'; \
    echo '    ExpiresByType application/javascript "access plus 1 month"'; \
    echo '    ExpiresByType font/woff2 "access plus 1 year"'; \
    echo '</IfModule>'; \
    echo ''; \
    echo '<IfModule mod_headers.c>'; \
    echo '    <FilesMatch "\.(webp|avif|jpg|jpeg|png|gif|ico|css|js|woff2)$">'; \
    echo '        Header set Cache-Control "public, max-age=31536000, immutable"'; \
    echo '    </FilesMatch>'; \
    echo '</IfModule>'; \
} > /etc/apache2/conf-available/performance.conf \
    && a2enconf performance

# PHP configuration for uploads
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 60'; \
} > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

EXPOSE 80
