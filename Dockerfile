FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libcurl4-openssl-dev libpng-dev \
    && docker-php-ext-install pdo_pgsql curl gd \
    && rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork headers rewrite \
    && apache2ctl -t \
    && apache2ctl -M \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

RUN sed -i 's/\r$//' /var/www/html/docker-start.sh \
    && mkdir -p /var/www/html/data/sessions \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod -R 770 /var/www/html/data \
    && chmod +x /var/www/html/docker-start.sh

EXPOSE 80

CMD ["/var/www/html/docker-start.sh"]
