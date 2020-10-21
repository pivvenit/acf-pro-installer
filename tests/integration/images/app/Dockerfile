ARG COMPOSER_VERSION=2@sha256:3a93c5674bce0938ba2166f23b27e54378da552bd19986e4ad80aabc73a467e6
ARG PHP_VERSION=7.4-alpine@sha256:862f99ef809039aae5450d93a58faead3b23a83b75e0f013b18c36990bbf80bf
FROM composer:${COMPOSER_VERSION} as composer

FROM php:${PHP_VERSION} as php
RUN apk update && apk add ca-certificates && rm -rf /var/cache/apk/*
COPY test.crt /usr/local/share/ca-certificates/test.crt
RUN update-ca-certificates
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY packages.json /registry/packages.json
WORKDIR /plugin
COPY release /plugin
WORKDIR /app
CMD composer install --no-dev --no-scripts --no-progress --no-suggest
