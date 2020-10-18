ARG COMPOSER_VERSION=2@sha256:3a93c5674bce0938ba2166f23b27e54378da552bd19986e4ad80aabc73a467e6
ARG PHP_VERSION=8.0-rc-alpine@sha256:05fcc4ba0971123f54cfa749c59d53c9d5bcce7cd6657f5f162ed0331de9e0f7
FROM composer:${COMPOSER_VERSION} as composer


FROM php:${PHP_VERSION} as php
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /plugin
COPY release /plugin
WORKDIR /plugin
CMD composer install --no-progress && composer run test
