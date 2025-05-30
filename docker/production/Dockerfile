# Build Stage for PHP dependencies
FROM composer:2 AS composer
WORKDIR /app
COPY . .
RUN composer install --no-interaction --optimize-autoloader

# Build Stage for Node.js dependencies and assets
FROM node:20-alpine AS node
WORKDIR /app
COPY . .

RUN npm install && npm ci && \
    npx vite build \
    && rm -rf node_modules

# Final production image
FROM alpine:3.19

LABEL maintainer="Dean Ward"

WORKDIR /var/www/html

ENV TZ=UTC
ENV SUPERVISOR_PHP_FPM_COMMAND="/usr/sbin/php-fpm83 -F"
ENV SUPERVISOR_CADDY_COMMAND="/usr/sbin/caddy run --config /etc/caddy/Caddyfile"
ENV SUPERVISOR_PHP_USER="vito"

# Enable community repository for PHP packages
RUN echo "http://dl-cdn.alpinelinux.org/alpine/v3.19/community" >> /etc/apk/repositories

# Install production requirements
RUN apk add --no-cache \
    curl \
    su-exec \
    supervisor \
    cronie \
    php83 \
    php83-pdo_pgsql \
    php83-pdo_sqlite \
    php83-gd \
    php83-curl \
    php83-pecl-mongodb \
    php83-imap \
    php83-pdo_mysql \
    php83-mbstring \
    php83-xml \
    php83-zip \
    php83-bcmath \
    php83-soap \
    php83-intl \
    php83-ldap \
    php83-pecl-redis \
    php83-pecl-memcached \
    php83-phar \
    php83-openssl \
    php83-json \
    php83-dom \
    php83-tokenizer \
    php83-fileinfo \
    tzdata \
    libcap \
    zip \
    php83-fpm \
    caddy \
    && ln -s /usr/bin/php83 /usr/bin/php \
    && rm -rf /var/cache/apk/*

# Create necessary directories
RUN mkdir -p /run/php /var/www/html/public /etc/php83/php-fpm.d /etc/caddy

COPY ./docker/production/www.conf /etc/php83/php-fpm.d/www.conf
COPY ./docker/production/Caddyfile /etc/caddy/Caddyfile

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure PHP capabilities
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php83

# Create vito user and group
RUN addgroup vito && \
    adduser -D -G vito vito

# Set permissions
RUN chown -R vito:vito /run/php

# Copy necessary files from build stages and local context
COPY --chown=vito:vito . /var/www/html/
COPY --from=composer /app/vendor /var/www/html/vendor
COPY --from=node /app/public/build /var/www/html/public/build
RUN rm -rf /var/www/html/.env /var/www/html/.git /var/www/html/node_modules /var/www/html/storage

# Set up Laravel scheduler cron job
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" | crontab -u vito -

# Copy configuration files
COPY ./docker/production/start-container /usr/local/bin/start-container
COPY ./docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/production/php.ini /etc/php83/conf.d/99-vito.ini
RUN chmod +x /usr/local/bin/start-container

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]