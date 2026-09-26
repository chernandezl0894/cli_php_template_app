FROM php:8.2-cli-alpine

RUN apk add --no-cache sqlite-dev \
    && docker-php-ext-install pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} -S app 2>/dev/null || true \
    && adduser -u ${UID} -S app -G app 2>/dev/null || true \
    && mkdir -p /tmp/composer \
    && chown -R ${UID}:${GID} /tmp/composer \
    && mkdir -p /app/database \
    && chown -R ${UID}:${GID} /app/database

WORKDIR /app
