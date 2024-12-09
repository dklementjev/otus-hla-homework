FROM tarantool/tarantool:3.2

RUN mkdir -p -m 0755 /srv/tarantool && \
    chown -R tarantool /srv/tarantool

COPY --chown=tarantool --chmod=0755 ./services/tarantool/docker-entrypoint.sh /
COPY --chown=tarantool --chmod=0755 ./services/tarantool/config /opt/tarantool/

USER tarantool
