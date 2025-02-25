FROM php:8.3-apache
WORKDIR /var/www/html
COPY codis/ /var/www/html/
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/
