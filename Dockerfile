FROM php:8.2-apache

# Bypass apt-get sepenuhnya — pakai installer yang download pre-compiled binary
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd zip pdo_sqlite mbstring \
    && a2enmod rewrite \
    && rm -f /usr/local/bin/install-php-extensions

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY app/ .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p data/mpdf_tmp uploads pdfs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 data uploads pdfs

COPY apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
