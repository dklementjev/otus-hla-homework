### Build
FROM bitnami/php-fpm:8.2 AS php-fpm-ext-build
RUN apt update &&  \
    apt install -y build-essential autoconf librabbitmq-dev librabbitmq4
ENV PHP_EXT_CONFIG_PATH_RUN=/opt/bitnami/php/etc/conf.d

RUN pecl install igbinary && \
    echo 'extension=igbinary.so' > ${PHP_EXT_CONFIG_PATH_RUN}/10-igbinary.ini

# TODO: enable igbinary
RUN pecl install redis && \
    echo 'extension=redis.so' > ${PHP_EXT_CONFIG_PATH_RUN}/20-redis.ini

RUN echo 'extension=pdo_pgsql.so' > ${PHP_EXT_CONFIG_PATH_RUN}/20-pdo_pgsql.ini

RUN pecl install amqp && \
    echo 'extension=amqp.so'  > ${PHP_EXT_CONFIG_PATH_RUN}/20-amqp.ini

### Runtime
FROM bitnami/php-fpm:8.2
RUN groupadd --system --gid 1000 www && \
    useradd --gid 1000 --no-create-home --uid 1000 -G daemon --home-dir /app www && \
    chgrp -R daemon /opt/bitnami/php/{var,tmp,logs} && \
    chmod g+w /opt/bitnami/php/{var,tmp,logs}

ARG PHP_EXT_MODULE_PATH_BUILD=/opt/bitnami/php/lib/php/extensions
ARG PHP_EXT_CONFIG_PATH_BUILD=/opt/bitnami/php/etc/conf.d
ARG PHP_EXT_MODULE_PATH_RUN=/opt/bitnami/php/lib/php/extensions
ARG PHP_EXT_CONFIG_PATH_RUN=/opt/bitnami/php/etc/conf.d
ARG USR_LIB_ARCH=/usr/lib/x86_64-linux-gnu

COPY --from=php-fpm-ext-build \
  ${PHP_EXT_MODULE_PATH_BUILD}/igbinary.so \
  ${PHP_EXT_MODULE_PATH_BUILD}/redis.so \
  ${PHP_EXT_MODULE_PATH_BUILD}/amqp.so \
  ${PHP_EXT_MODULE_PATH_RUN}/

COPY --from=php-fpm-ext-build \
  ${USR_LIB_ARCH}/librabbitmq.so \
  ${USR_LIB_ARCH}/librabbitmq.so.* \
  ${USR_LIB_ARCH}/

COPY --from=php-fpm-ext-build \
  ${PHP_EXT_CONFIG_PATH_BUILD}/10-igbinary.ini \
  ${PHP_EXT_CONFIG_PATH_BUILD}/20-amqp.ini \
  ${PHP_EXT_CONFIG_PATH_BUILD}/20-redis.ini \
  ${PHP_EXT_CONFIG_PATH_BUILD}/20-pdo_pgsql.ini \
  ${PHP_EXT_CONFIG_PATH_RUN}/

ENV HISTFILE=/tmp/.bash_history
USER 1000
