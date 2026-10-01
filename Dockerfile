FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
 && a2enmod headers \
 && printf 'upload_max_filesize=50M\npost_max_size=50M\nmemory_limit=256M\n' > /usr/local/etc/php/conf.d/app.ini

COPY docker/app.conf /etc/apache2/conf-available/app.conf
RUN a2enconf app

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY app/ /var/www/html/
RUN chmod +x /usr/local/bin/entrypoint.sh && chown -R www-data:www-data /var/www/html

CMD ["/usr/local/bin/entrypoint.sh"]
