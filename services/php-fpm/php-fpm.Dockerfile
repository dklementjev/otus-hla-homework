FROM bitnami/php-fpm:8.2
RUN apt-get update && \
    apt-get install -y php-pgsql && \
    ln -s /etc/php/8.2/mods-available/pdo_pgsql.ini /opt/bitnami/php/etc/conf.d/pdo_pgsql.ini && \
    apt-get clean