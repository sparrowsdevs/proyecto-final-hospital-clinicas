
FROM php:8.3-apache


RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers


COPY docker/php/sigsm.ini /usr/local/etc/php/conf.d/sigsm.ini
COPY docker/apache/sigsm.conf /etc/apache2/conf-available/sigsm.conf
RUN a2enconf sigsm


COPY --chown=www-data:www-data . /var/www/html/


RUN rm -rf /var/www/html/docker /var/www/html/Dockerfile

EXPOSE 80
