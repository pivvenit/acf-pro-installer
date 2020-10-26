ARG COMPOSER_VERSION=2@sha256:aec14858305c64f735fd214b01bb0dc4327545d94bac6d7d4f28d3c486945b0a
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
CMD php -v && composer --version && composer install --no-dev --no-scripts --no-progress
