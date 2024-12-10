FROM denoland/deno:2.1.3

EXPOSE 3000

USER deno

WORKDIR /app

COPY --chown=deno package.json package-lock.json ./
RUN deno install

COPY --chown=deno .env ./
