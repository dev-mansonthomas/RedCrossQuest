# syntax=docker/dockerfile:1.7
# -----------------------------------------------------------------------------
# RedCrossQuest - PHP-FPM image mirroring the Google App Engine `php83` runtime
# -----------------------------------------------------------------------------
# GAE `php83` = Ubuntu + Nginx + PHP-FPM + Google `serve` wrapper.
# We reproduce the PHP side here; Nginx lives in its own image.
#
# Forward compatibility: bump PHP_VERSION to 8.5 once GAE exposes `php85`.
#   docker build --build-arg PHP_VERSION=8.5 ...
# -----------------------------------------------------------------------------
ARG PHP_VERSION=8.3

# ============================== base stage ===================================
FROM php:${PHP_VERSION}-fpm-bookworm AS base

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    PHP_MEMORY_LIMIT=512M

# System libs required by GAE-equivalent extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip zip curl ca-certificates \
        libzip-dev libbz2-dev libsodium-dev libssl-dev \
        libonig-dev libicu-dev libxml2-dev libgmp-dev \
        pkg-config autoconf build-essential \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Native PHP extensions required by composer.json (ext-PDO, ext-bz2, ext-sodium,
# ext-zip, ext-json, ext-gmp — pulled by sendgrid/sendgrid + starkbank/ecdsa).
# `json` and `sodium` are bundled since PHP 8.x; we still configure `sodium`.
RUN docker-php-ext-configure zip \
 && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_mysql bz2 zip sodium intl opcache bcmath gmp

# PECL extensions used in production (Stackdriver / Firestore gRPC client)
# gRPC is a huge C++ codebase (~3500 source files) that consumes ~1.5 GB of
# RAM per parallel job. Allocate at least 16 GB to Docker Desktop before
# building, otherwise drop this to `-j2` to avoid OOM.
# protobuf is pinned to 4.x to match the ABI of the composer
# `google/protobuf` 4.x package (google/gax 1.42 no longer calls the
# removed FieldDescriptor::getLabel(), so the 4.31.1 pin is not required
# anymore — but staying on 4.x keeps compiled-in message classes stable).
RUN MAKEFLAGS="-j$(nproc)" pecl install grpc protobuf-4.31.1 \
 && docker-php-ext-enable grpc protobuf

# Composer (pinned via official multi-arch image)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP-FPM tuning aligned with GAE F1/F2 instances
COPY docker/php/php.ini       /usr/local/etc/php/conf.d/zz-rcq.ini
COPY docker/php/www.conf      /usr/local/etc/php-fpm.d/zz-rcq.conf

# PHP-DI compiled container cache directory (see server/public/rest/index.php:29)
RUN mkdir -p /tmp/php-di-compiled && chown -R www-data:www-data /tmp/php-di-compiled

WORKDIR /app/server

# Non-privileged user matching php-fpm default
USER www-data

EXPOSE 9000
CMD ["php-fpm", "-F"]

# ============================== dev stage ====================================
FROM base AS dev

USER root

# Xdebug for step-debugging from IntelliJ (see install doc §Xdebug)
RUN MAKEFLAGS="-j$(nproc)" pecl install xdebug \
 && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# Phinx CLI convenience (already a composer dep but we expose a system link)
RUN ln -sf /app/server/vendor/bin/phinx /usr/local/bin/phinx || true

USER www-data
