FROM php:8.5-cli

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${GID} appuser \
&& useradd -u ${UID} -g appuser -m appuser

WORKDIR /var/www

USER appuser
