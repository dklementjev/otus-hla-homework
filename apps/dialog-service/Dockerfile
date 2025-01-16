FROM node:22-slim

EXPOSE 3000

USER node

WORKDIR /app

COPY --chown=node package.json package-lock.json ./
RUN npm ci

COPY --chown=node .env ./
