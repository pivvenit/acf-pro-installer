FROM composer@sha256:260b64250820be8c5c7e731a5e83a311bf8c3a2c1f067c88c1213775e6257666 as composer

FROM php:7.4-alpine@sha256:862f99ef809039aae5450d93a58faead3b23a83b75e0f013b18c36990bbf80bf
RUN apk update && apk add ca-certificates && rm -rf /var/cache/apk/*
COPY test.crt /usr/local/share/ca-certificates/test.crt
RUN update-ca-certificates
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY packages.json /registry/packages.json
WORKDIR /plugin
COPY release /plugin
WORKDIR /app
CMD composer install --no-dev --no-scripts --no-progress --no-suggest
