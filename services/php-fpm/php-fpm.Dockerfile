FROM bitnami/php-fpm:8.2
RUN apt-get update && \
    apt-get install -y php-pgsql && \
    ln -s /etc/php/8.2/mods-available/pdo_pgsql.ini /opt/bitnami/php/etc/conf.d/pdo_pgsql.ini && \
    apt-get clean && \
    groupadd --system --gid 1000 www && \
    useradd --gid 1000 --no-create-home --uid 1000 -G daemon --home-dir /app www && \
    chgrp -R daemon /opt/bitnami/php/{var,tmp,logs} && \
    chmod g+w /opt/bitnami/php/{var,tmp,logs}


ENV HISTFILE=/tmp/.bash_history
USER 1000